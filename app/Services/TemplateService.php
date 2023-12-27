<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class TemplateService
{
    public static function hash(string $path, $disk = null) : ?string
    {
        if (!Storage::disk($disk)->exists($path)) return null;
        return hash_file('sha256', Storage::disk($disk)->path($path));
    }

    public static function bindings(string $path, $disk = null, $json = false) : array|string
    {
        if (!Storage::disk($disk)->exists($path)) return [];

        $proc = new TemplateProcessor(Storage::disk($disk)->path($path));
        $bindings = $proc->getVariables();

        $rowMacros = self::locateMacros('row', $bindings);
        $bindings = self::removeMacros($bindings, $rowMacros);
        $rowGroups = self::groupRowMacros($rowMacros);

        $blockMacros = self::locateMacros('block', $bindings);
        $bindings = self::removeMacros($bindings, $blockMacros);
        $blockGroups = self::groupBlockMacros($blockMacros);

        $result = [
            'rows' => $rowGroups,
            'blocks' => $blockGroups,
            'bindings' => $bindings,
        ];

        return $json ? json_encode($result) : $result;
    }

    protected static function locateMacros($type, array $macros)
    {
        $rows = preg_grep("/{$type}__(.*)\.?(.*)/i", $macros);
        return array_values($rows);
    }

    protected static function removeMacros(array $bindings, array $macros)
    {
        return array_values(array_filter($bindings, function($binding) use ($macros) {
            return !in_array($binding, $macros);
        }));
    }

    /**
     * Groups row macros
     *
     * @param array $macros
     * @return array
     */
    protected static function groupRowMacros(array $macros)
    {
        $groups = [];
        foreach ($macros as $macro)
        {
            if (strpos($macro, '.')) {
                list($macro, $cell) = explode('.', $macro);
                $groups[$macro][] = $macro.'.'.$cell;
            } else {
                // Row macro has at least one element, the one initializing it.
                $groups[$macro][] = $macro;
            }
        }
        return $groups;
    }

    /**
     * Groups block macros
     *
     * @param array $macros
     * @return array
     */
    protected static function groupBlockMacros(array $macros)
    {
        $groups = [];
        foreach ($macros as $macro)
        {
            // Catch closing macro
            $macro = ltrim($macro, '/');

            if (strpos($macro, '.')) {
                list($macro, $cell) = explode('.', $macro);
                $groups[$macro][] = $macro.'.'.$cell;
            } elseif(!isset($groups[$macro])) {
                // Block macro can be empty inside
                $groups[$macro] = [];
            }
        }
        return $groups;
    }


}
