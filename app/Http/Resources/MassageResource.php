<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MassageResource extends JsonResource
{
    public $status;
    public $massage;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function __construct($resource, $status, $massage)
    {
        parent::__construct($resource);
        $this->status = $status;
        $this->massage = $massage;
    }
    public function toArray(Request $request): array
    {
        return[
            'status' => $this->status,
            'massage' => $this->massage,
            'data' => $this->resource
        ];
    }
}
