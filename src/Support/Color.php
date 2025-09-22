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
                'text' => 'text-green-400 dark:text-green-500 hover:text-green-300 dark:hover:text-green-600',
                'background' => 'bg-green-900/50 dark:bg-green-900 hover:bg-green-900/60 dark:hover:bg-green-900',
            ],

            ColorEnum::BLUE => [
                'text' => 'text-blue-400 dark:text-blue-500 hover:text-blue-300 dark:hover:text-blue-600',
                'background' => 'bg-blue-900/50 dark:bg-blue-900 hover:bg-blue-900/60 dark:hover:bg-blue-900',
            ],

            ColorEnum::INDIGO => [
                'text' => 'text-indigo-400 dark:text-indigo-500 hover:text-indigo-300 dark:hover:text-indigo-600',
                'background' => 'bg-indigo-900/50 dark:bg-indigo-900 hover:bg-indigo-900/60 dark:hover:bg-indigo-900',
            ],

            ColorEnum::PURPLE => [
                'text' => 'text-purple-400 dark:text-purple-500 hover:text-purple-300 dark:hover:text-purple-600',
                'background' => 'bg-purple-900/50 dark:bg-purple-900 hover:bg-purple-900/60 dark:hover:bg-purple-900',
            ],

            ColorEnum::PINK => [
                'text' => 'text-pink-400 dark:text-pink-500 hover:text-pink-300 dark:hover:text-pink-600',
                'background' => 'bg-pink-900/50 dark:bg-pink-900 hover:bg-pink-900/60 dark:hover:bg-pink-900',
            ],

            ColorEnum::TEAL => [
                'text' => 'text-teal-400 dark:text-teal-500 hover:text-teal-300 dark:hover:text-teal-600',
                'background' => 'bg-teal-900/50 dark:bg-teal-900 hover:bg-teal-900/60 dark:hover:bg-teal-900',
            ],

            ColorEnum::CYAN => [
                'text' => 'text-cyan-400 dark:text-cyan-500 hover:text-cyan-300 dark:hover:text-cyan-600',
                'background' => 'bg-cyan-900/50 dark:bg-cyan-900 hover:bg-cyan-900/60 dark:hover:bg-cyan-900',
            ],

            ColorEnum::LIME => [
                'text' => 'text-lime-400 dark:text-lime-500 hover:text-lime-300 dark:hover:text-lime-600',
                'background' => 'bg-lime-900/50 dark:bg-lime-900 hover:bg-lime-900/60 dark:hover:bg-lime-900',
            ],

            ColorEnum::EMERALD => [
                'text' => 'text-emerald-400 dark:text-emerald-500 hover:text-emerald-300 dark:hover:text-emerald-600',
                'background' => 'bg-emerald-900/50 dark:bg-emerald-900 hover:bg-emerald-900/60 dark:hover:bg-emerald-900',
            ],

            ColorEnum::SKY => [
                'text' => 'text-sky-400 dark:text-sky-500 hover:text-sky-300 dark:hover:text-sky-600',
                'background' => 'bg-sky-900/50 dark:bg-sky-900 hover:bg-sky-900/60 dark:hover:bg-sky-900',
            ]
        };
    }
}
