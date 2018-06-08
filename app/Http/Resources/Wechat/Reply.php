<?php

namespace App\Http\Resources\Wechat;

use App\Models\Wechat\WechatMaterial;
use Illuminate\Http\Resources\Json\Resource;
use function Symfony\Component\Debug\Tests\testHeader;

class Reply extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $contents = [];

        foreach ($this->contents as $content) {
            if ($content['type'] === 'text') {
                $contents[] = $content;
            } else {
                $material = WechatMaterial::where(['media_id' => $content['content'], 'type' => $content['type']])->first();
                if ($material) {
                    if ($material->type === 'news') {
                        $contents[] = array(
                            'type' => $content['type'],
                            'thumb_path' => $material->content['news_item'][0]['thumb_path'] ?? null,
                            'title' => $material->content['news_item'][0]['title'] ?? null,
                            'digest' => $material->content['news_item'][0]['digest'] ?? null,
                            'media_id' => $material->media_id,
                            'news_content' => $content['news_content'] ?? null,
                        );
                    } else {
                        $contents[] = array(
                            'type' => $content['type'],
                            'title' => $material->content['name'] ?? null,
                            'url' => $material->content['path'] ?? null,
                            'media_id' => $material->media_id,
                        );
                    }
                }
            }
        }

        return [
            'id' => $this->id,
            'rule_name' => $this->rule_name,
            'keywords' => $this->keywords,
            'contents' => $contents,
            'is_reply_all' => $this->is_reply_all,
            'is_open' => $this->is_open,
            'created_at' => date_format(date_create($this->created_at), 'Y-m-d H:i:s'),
            'updated_at' => date_format(date_create($this->updated_at), 'Y-m-d H:i:s'),
        ];
    }
}
