<?php

namespace App\Http\Resources;

use App\Models\facilities;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id"=>$this->id,
            "id_facility"=>$this->id_facility,
            "id_user"=>$this->id_user,
            "report"=>$this->report,
            "created_at"=>$this->created_at->format('d/m/Y'),
            "updated_at"=>$this->updated_at->format('d/m/Y'),
            "facility"=>FacilityResource::collection(facilities::where('id',$this->id_facility)->get())
        ];
    }
}
