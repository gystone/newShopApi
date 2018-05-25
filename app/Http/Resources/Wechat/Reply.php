<?php

namespace App\Http\Resources;

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
                    $contents[] = array(
                        'type' => $content['type'],
                        'url' => $material->content['path']
                    );
                }
            }
        }

        return [
            'id' => $this->id,
            'rule_name' => 'dsf',
            'keywords' => $this->keywords,
            'contents' => $contents,
            'is_reply_all' => $this->is_reply_all,
            'is_open' => $this->is_open,
            'created_at' => date_format(date_create($this->created_at), 'Y-m-d H:i:s'),
            'updated_at' => date_format(date_create($this->updated_at), 'Y-m-d H:i:s'),
        ];
    }
}
