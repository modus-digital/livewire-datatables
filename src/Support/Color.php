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
                'text' => 'text-red-700 dark:text-red-300 hover:text-red-800 dark:hover:text-red-200',
                'background' => 'bg-red-100 dark:bg-red-900/50 hover:bg-red-200 dark:hover:bg-red-900/60',
                'ring' => 'focus:ring-red-500 dark:focus:ring-red-500 focus:border-red-500 dark:focus:border-red-500',
                'border' => 'border-red-200 dark:border-red-700',
            ],

            ColorEnum::ORANGE => [
                'text' => 'text-orange-700 dark:text-orange-300 hover:text-orange-800 dark:hover:text-orange-200',
                'background' => 'bg-orange-100 dark:bg-orange-900/50 hover:bg-orange-200 dark:hover:bg-orange-900/60',
                'ring' => 'focus:ring-orange-500 dark:focus:ring-orange-500 focus:border-orange-500 dark:focus:border-orange-500',
                'border' => 'border-orange-200 dark:border-orange-700',
            ],

            ColorEnum::YELLOW => [
                'text' => 'text-yellow-700 dark:text-yellow-300 hover:text-yellow-800 dark:hover:text-yellow-200',
                'background' => 'bg-yellow-100 dark:bg-yellow-900/50 hover:bg-yellow-200 dark:hover:bg-yellow-900/60',
                'ring' => 'focus:ring-yellow-500 dark:focus:ring-yellow-500 focus:border-yellow-500 dark:focus:border-yellow-500',
                'border' => 'border-yellow-200 dark:border-yellow-700',
            ],

            ColorEnum::GREEN => [
                'text' => 'text-green-700 dark:text-green-300 hover:text-green-800 dark:hover:text-green-200',
                'background' => 'bg-green-100 dark:bg-green-900/50 hover:bg-green-200 dark:hover:bg-green-900/60',
                'ring' => 'focus:ring-green-500 dark:focus:ring-green-500 focus:border-green-500 dark:focus:border-green-500',
                'border' => 'border-green-200 dark:border-green-700',
            ],

            ColorEnum::BLUE => [
                'text' => 'text-blue-700 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-200',
                'background' => 'bg-blue-100 dark:bg-blue-900/50 hover:bg-blue-200 dark:hover:bg-blue-900/60',
                'ring' => 'focus:ring-blue-500 dark:focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500',
                'border' => 'border-blue-200 dark:border-blue-700',
            ],

            ColorEnum::INDIGO => [
                'text' => 'text-indigo-700 dark:text-indigo-300 hover:text-indigo-800 dark:hover:text-indigo-200',
                'background' => 'bg-indigo-100 dark:bg-indigo-900/50 hover:bg-indigo-200 dark:hover:bg-indigo-900/60',
                'ring' => 'focus:ring-indigo-500 dark:focus:ring-indigo-500 focus:border-indigo-500 dark:focus:border-indigo-500',
                'border' => 'border-indigo-200 dark:border-indigo-700',
            ],

            ColorEnum::PURPLE => [
                'text' => 'text-purple-700 dark:text-purple-300 hover:text-purple-800 dark:hover:text-purple-200',
                'background' => 'bg-purple-100 dark:bg-purple-900/50 hover:bg-purple-200 dark:hover:bg-purple-900/60',
                'ring' => 'focus:ring-purple-500 dark:focus:ring-purple-500 focus:border-purple-500 dark:focus:border-purple-500',
                'border' => 'border-purple-200 dark:border-purple-700',
            ],

            ColorEnum::PINK => [
                'text' => 'text-pink-700 dark:text-pink-300 hover:text-pink-800 dark:hover:text-pink-200',
                'background' => 'bg-pink-100 dark:bg-pink-900/50 hover:bg-pink-200 dark:hover:bg-pink-900/60',
                'ring' => 'focus:ring-pink-500 dark:focus:ring-pink-500 focus:border-pink-500 dark:focus:border-pink-500',
                'border' => 'border-pink-200 dark:border-pink-700',
            ],

            ColorEnum::TEAL => [
                'text' => 'text-teal-700 dark:text-teal-300 hover:text-teal-800 dark:hover:text-teal-200',
                'background' => 'bg-teal-100 dark:bg-teal-900/50 hover:bg-teal-200 dark:hover:bg-teal-900/60',
                'ring' => 'focus:ring-teal-500 dark:focus:ring-teal-500 focus:border-teal-500 dark:focus:border-teal-500',
                'border' => 'border-teal-200 dark:border-teal-700',
            ],

            ColorEnum::CYAN => [
                'text' => 'text-cyan-700 dark:text-cyan-300 hover:text-cyan-800 dark:hover:text-cyan-200',
                'background' => 'bg-cyan-100 dark:bg-cyan-900/50 hover:bg-cyan-200 dark:hover:bg-cyan-900/60',
                'ring' => 'focus:ring-cyan-500 dark:focus:ring-cyan-500 focus:border-cyan-500 dark:focus:border-cyan-500',
                'border' => 'border-cyan-200 dark:border-cyan-700',
            ],

            ColorEnum::LIME => [
                'text' => 'text-lime-700 dark:text-lime-300 hover:text-lime-800 dark:hover:text-lime-200',
                'background' => 'bg-lime-100 dark:bg-lime-900/50 hover:bg-lime-200 dark:hover:bg-lime-900/60',
                'ring' => 'focus:ring-lime-500 dark:focus:ring-lime-500 focus:border-lime-500 dark:focus:border-lime-500',
                'border' => 'border-lime-200 dark:border-lime-700',
            ],

            ColorEnum::EMERALD => [
                'text' => 'text-emerald-700 dark:text-emerald-300 hover:text-emerald-800 dark:hover:text-emerald-200',
                'background' => 'bg-emerald-100 dark:bg-emerald-900/50 hover:bg-emerald-200 dark:hover:bg-emerald-900/60',
                'ring' => 'focus:ring-emerald-500 dark:focus:ring-emerald-500 focus:border-emerald-500 dark:focus:border-emerald-500',
                'border' => 'border-emerald-200 dark:border-emerald-700',
            ],

            ColorEnum::SKY => [
                'text' => 'text-sky-700 dark:text-sky-300 hover:text-sky-800 dark:hover:text-sky-200',
                'background' => 'bg-sky-100 dark:bg-sky-900/50 hover:bg-sky-200 dark:hover:bg-sky-900/60',
                'ring' => 'focus:ring-sky-500 dark:focus:ring-sky-500 focus:border-sky-500 dark:focus:border-sky-500',
                'border' => 'border-sky-200 dark:border-sky-700',
            ]
        };
    }
}
