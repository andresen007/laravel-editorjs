<?php

namespace AlAminFirdows\LaravelEditorJs;

use EditorJS\EditorJS;
use EditorJS\EditorJSException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;

class LaravelEditorJs
{
    /**
     * Render blocks
     *
     * @param string $data
     * @return string
     */
    public function render(string $data) : string
    {
        try {
            
            $configJson = json_encode(config('laravel_editorjs.config') ?: []);
            $editor = new EditorJS($data, $configJson);
            $renderedBlocks = [];

            foreach ($editor->getBlocks() as $block) {
                
                if ($block['type'] === 'columns') {
                    $columnContents = [];
                
                    foreach ($block['data']['cols'] as $col) {
                        $tempColumnContent = ''; // Midlertidig variabel for at samle kolonneindhold
                
                        foreach ($col['blocks'] as $colBlock) {
                            $viewName = "laravel_editorjs::blocks." . Str::snake($colBlock['type'], '-');
                
                            if (!View::exists($viewName)) {
                                $viewName = 'laravel_editorjs::blocks.not-found';
                            }
                
                            $tempColumnContent .= View::make($viewName, [
                                'type' => $colBlock['type'],
                                'data' => $colBlock['data']
                            ])->render();
                        }
                
                        $columnContents[] = $tempColumnContent; // Tilføjer samlet indhold til columnContents
                    }
                
                    $renderedBlocks[] = View::make('laravel_editorjs::blocks.columns', [
                        'columns' => $columnContents
                    ])->render();
                }
                 else if ($block['type'] === 'notColumns') {
                    $viewName = "laravel_editorjs::blocks." . Str::snake($block['data']['type'], '-');
                    
                    if (! View::exists($viewName)) {
                        $viewName = 'laravel_editorjs::blocks.not-found';
                    }

                    $renderedBlocks[] = View::make($viewName, [
                        'type' => $block['data']['type'],
                        'data' => $block['data']['data']
                    ])->render();
                }
            }

            return implode($renderedBlocks);

        } catch (EditorJSException $e) {
            throw new \Exception($e->getMessage());
        }
    }




}
