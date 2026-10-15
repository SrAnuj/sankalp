<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NaamLibraryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'items' => $this->collection->map(function ($naam) {
                return [
                    'id' => $naam->id,
                    'name' => $naam->name,
                    'slug' => $naam->slug,
                    'description' => $naam->description,
                    'language' => $naam->language,
                    'scripture' => $naam->scripture,
                    'image' => $naam->image,
                    'is_active' => (bool) $naam->is_active,
                    'sort_order' => $naam->sort_order,
                    'created_at' => $naam->created_at?->toISOString(),
                    'updated_at' => $naam->updated_at?->toISOString(),
                ];
            }),
        ];
    }
}
