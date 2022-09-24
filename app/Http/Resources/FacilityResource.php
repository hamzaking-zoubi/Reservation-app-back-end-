<?php

namespace App\Http\Resources;

use App\Models\photos_fac;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
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
            "id_user"=>$this->id_user,
            "name"=>$this->name,
            "location"=>$this->location,
            "description"=>$this->description,
            "type"=>$this->type,
            "cost"=>$this->cost,
            "rate"=>$this->rate,
            "num_guest"=>$this->num_guest,
            "num_room"=>$this->num_room,
            "wifi"=>$this->wifi,
            "coffee_machine"=>$this->coffee_machine,
            "air_condition"=>$this->air_condition,
            "tv"=>$this->tv,
            "fridge"=>$this->fridge,
            "created_at"=>$this->created_at->format('d/m/Y'),
            "updated_at"=>$this->updated_at->format('d/m/Y'),
            "photos"=>PhotoResource::collection(photos_fac::where('id_facility',$this->id)->get())
        ];
    }
}
