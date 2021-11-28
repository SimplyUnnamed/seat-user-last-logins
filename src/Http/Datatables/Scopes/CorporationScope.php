<?php


namespace SimplyUnnamed\Seat\UserLastLogin\Http\Datatables\Scopes;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Corporation\CorporationRole;
use Yajra\DataTables\Contracts\DataTableScope;

class CorporationScope implements DataTableScope
{

    private $ability;

    private $requested_corporations;

    public function __construct(?string $ability = null, ?array $corporation_ids = null)
    {
        $this->ability = $ability;
        $this->requested_corporations = $corporation_ids;
    }

    public function apply($query)
    {
        //$table = $query->getQuery()->from;

        if (!is_null($this->requested_corporations)) {
            $query->whereHas('characters', function (Builder $query) {
                $query->whereHas('affiliation', function (Builder $query) {
                    $query->whereIn('corporation_id', $this->requested_corporations);
                });
            });
            return $query;
        }

        if(auth()->user()->isAdmin())
            return $query;

        $permissions = auth()->roles()->with('permissions')->get()
            ->pluck('permissions')
            ->flatten()
            ->filter(function($permission){
                if(empty($this->ability))
                    return strpos($permission->title, 'corporation.')===0;
                return $permission->title == $this->ability;
            });

        if($permissions->filter(fn ($permissions) => ! $permissions->hasFilters() )->isNotEmpty())
            return $query;

        $map = $permissions->map(function($permission){
            $filters = json_decode($permission->pivot->filters);

            return [
                'corporations' => collect($filters->corporation ?? [])->pluck('id')->toArray(),
                'alliances' => collect($filters->alliance ?? [])->pluck('id')->toArray(),
            ];
        });

        $owner_range = CorporationInfo::whereIn('ceo_id', auth()->user()->associatedCharacterIds())
            ->select('corporation_id')->get()->pluck('corporation_id')->toArray();

        $corp_range = $map->pluck('corporations')->flatten()->toArray();

        $alliance_range = CorporationInfo::whereIn('alliance_id', $map->pluck('alliances')->platten()->toArray())
            ->select('corporation_id')->get()->pluck('corporation_id')->toArray();

        $associated_corporations = auth()->user()->characters->load('affiliation')->pluck('affiliation.corporation_id')->values()->toArray();
        $director_range = CorporationRole::whereIn('corporation_id', $associated_corporations)->whereIn('character_id', auth()->user()->associatedCharacterIds())
            ->where('role', 'Director')->where('type', 'roles')
            ->pluck('corporation_id')->values()->toArray();

        $corp_ids = array_merge($owner_range, $corp_range, $alliance_range, $director_range);

        if(!empty($corp_ids)){
            $query->whereHas('characters', function (Builder $query) use($corp_ids) {
                $query->whereHas('affiliation', function (Builder $query) use ($corp_ids) {
                    $query->whereIn('corporation_id', $corp_ids);
                });
            });
        }
        return $query;
    }
}