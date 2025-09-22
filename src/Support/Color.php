<?php

declare(strict_types=1);

namespace ModusDigital\LivewireDatatables\Support;

use ModusDigital\LivewireDatatables\Enums\Color as ColorEnum;

class Color
{
    public static function get(): ColorEnum
    {
        $color = config('livewire-datatables.color');
        $color = ColorEnum::tryFrom($color) ?? ColorEnum::INDIGO;

        return $color;
    }

    /**
     * Haal een kleurmap op gebaseerd op de base color uit de config.
     *
     * @return array<ColorEnum, string> Kleurvarianten
     */
    public static function getColorMap(): array
    {
        $baseColor = self::get();

        // Standaard Tailwind kleuren als voorbeeld, kan uitgebreid worden
        return match ($baseColor) {
            ColorEnum::RED => [
                'text' => 'text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300',
                'background' => 'bg-red-50 dark:bg-red-900/50 hover:bg-red-100 dark:hover:bg-red-900/60',
            ],

            ColorEnum::ORANGE => [
                'text' => 'text-orange-500 dark:text-orange-400 hover:text-orange-600 dark:hover:text-orange-300',
                'background' => 'bg-orange-50 dark:bg-orange-900/50 hover:bg-orange-100 dark:hover:bg-orange-900/60',
            ],

            ColorEnum::YELLOW => [
                'text' => 'text-yellow-500 dark:text-yellow-400 hover:text-yellow-600 dark:hover:text-yellow-300',
                'background' => 'bg-yellow-50 dark:bg-yellow-900/50 hover:bg-yellow-100 dark:hover:bg-yellow-900/60',
            ],

            ColorEnum::GREEN => [
                'text' => 'text-green-500 dark:text-green-400 hover:text-green-600 dark:hover:text-green-300',
                'background' => 'bg-green-50 dark:bg-green-900/50 hover:bg-green-100 dark:hover:bg-green-900/60',
            ],

            ColorEnum::BLUE => [
                'text' => 'text-blue-500 dark:text-blue-400 hover:text-blue-600 dark:hover:text-blue-300',
                'background' => 'bg-blue-50 dark:bg-blue-900/50 hover:bg-blue-100 dark:hover:bg-blue-900/60',
            ],

            ColorEnum::INDIGO => [
                'text' => 'text-indigo-500 dark:text-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-300',
                'background' => 'bg-indigo-50 dark:bg-indigo-900/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/60',
            ],

            ColorEnum::PURPLE => [
                'text' => 'text-purple-500 dark:text-purple-400 hover:text-purple-600 dark:hover:text-purple-300',
                'background' => 'bg-purple-50 dark:bg-purple-900/50 hover:bg-purple-100 dark:hover:bg-purple-900/60',
            ],

            ColorEnum::PINK => [
                'text' => 'text-pink-500 dark:text-pink-400 hover:text-pink-600 dark:hover:text-pink-300',
                'background' => 'bg-pink-50 dark:bg-pink-900/50 hover:bg-pink-100 dark:hover:bg-pink-900/60',
            ],

            ColorEnum::TEAL => [
                'text' => 'text-teal-500 dark:text-teal-400 hover:text-teal-600 dark:hover:text-teal-300',
                'background' => 'bg-teal-50 dark:bg-teal-900/50 hover:bg-teal-100 dark:hover:bg-teal-900/60',
            ],

            ColorEnum::CYAN => [
                'text' => 'text-cyan-500 dark:text-cyan-400 hover:text-cyan-600 dark:hover:text-cyan-300',
                'background' => 'bg-cyan-50 dark:bg-cyan-900/50 hover:bg-cyan-100 dark:hover:bg-cyan-900/60',
            ],

            ColorEnum::LIME => [
                'text' => 'text-lime-500 dark:text-lime-400 hover:text-lime-600 dark:hover:text-lime-300',
                'background' => 'bg-lime-50 dark:bg-lime-900/50 hover:bg-lime-100 dark:hover:bg-lime-900/60',
            ],

            ColorEnum::EMERALD => [
                'text' => 'text-emerald-500 dark:text-emerald-400 hover:text-emerald-600 dark:hover:text-emerald-300',
                'background' => 'bg-emerald-50 dark:bg-emerald-900/50 hover:bg-emerald-100 dark:hover:bg-emerald-900/60',
            ],

            ColorEnum::SKY => [
                'text' => 'text-sky-500 dark:text-sky-400 hover:text-sky-600 dark:hover:text-sky-300',
                'background' => 'bg-sky-50 dark:bg-sky-900/50 hover:bg-sky-100 dark:hover:bg-sky-900/60',
            ]
        };
    }
}
