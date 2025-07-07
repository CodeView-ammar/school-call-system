<?php

namespace App\Filament\Components;

use Filament\Forms\Components\ViewComponent;

class MapPicker extends ViewComponent
{
    protected string $view = 'filament.forms.components.map-picker';

    public static function make(string $name = 'location'): static
    {
        return parent::make($name);
    }

    public function getLatitudeField(): ?string
    {
        return $this->evaluate($this->latitudeField ?? 'latitude');
    }

    public function getLongitudeField(): ?string
    {
        return $this->evaluate($this->longitudeField ?? 'longitude');
    }

    public function latitudeField(string $field): static
    {
        $this->latitudeField = $field;
        return $this;
    }

    public function longitudeField(string $field): static
    {
        $this->longitudeField = $field;
        return $this;
    }

    public function defaultLocation(float $lat, float $lng): static
    {
        $this->defaultLat = $lat;
        $this->defaultLng = $lng;
        return $this;
    }

    public function zoom(int $zoom = 13): static
    {
        $this->zoom = $zoom;
        return $this;
    }

    public function height(string $height = '400px'): static
    {
        $this->height = $height;
        return $this;
    }

    public function getDefaultLat(): float
    {
        return $this->defaultLat ?? 24.7136; // الرياض
    }

    public function getDefaultLng(): float
    {
        return $this->defaultLng ?? 46.6753; // الرياض
    }

    public function getZoom(): int
    {
        return $this->zoom ?? 13;
    }

    public function getHeight(): string
    {
        return $this->height ?? '400px';
    }
}