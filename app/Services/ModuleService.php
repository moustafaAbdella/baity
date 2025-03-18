<?php

namespace App\Services;

use App\Traits\FileManagerTrait;

class ModuleService
{
    use FileManagerTrait;

    public function getAddData(Object $request): array
    {
        $data = [
            'module_name' => $request->module_name[array_search('default', $request->lang)],
            'icon' => $this->upload('module/', 'png', $request->file('icon')),
            'thumbnail' => $this->upload('module/', 'png', $request->file('thumbnail')),
            'module_type' => $request->module_type,
            'theme_id' => 1,
            'description' => $request->description[array_search('default', $request->lang)],
        ];

        switch ($request->module_type) {
            case 'ecommerce':
                $data['category_id'] = 'oIcaYzeDfQQ=';
                $data['authority_id'] = 'Nap4gA1tyeY=';
                break;

            case 'parcel':
                $data['category_id'] = 'rAy9UhMUw6Y=';
                $data['authority_id'] = 'J4DRtp+fYEM=';
                break;

            case 'grocery':
                $data['category_id'] = 'Nap4gA1tyeY=';
                $data['authority_id'] = 'w+mTCW1569Y=';
                break;

            case 'food':
                $data['category_id'] = 'NV25GlPuOnQ=';
                $data['authority_id'] = 'NV25GlPuOnQ=';
                break;

            case 'pharmacy':
                $data['category_id'] = 'w+mTCW1569Y=';
                $data['authority_id'] = 'oIcaYzeDfQQ=';
                break;

            default:
                // يمكن إضافة منطق افتراضي هنا إذا لم يطابق أي شرط
               return ('Invalid module type');
        }
        return $data;
    }
    public function getUpdateData(Object $request, object $module): array
    {
        return [
            'module_name' => $request->module_name[array_search('default', $request->lang)],
            'icon' => $request->has('icon') ? $this->updateAndUpload('module/', $module->icon, 'png', $request->file('icon')) : $module->icon,
            'thumbnail' => $request->has('thumbnail') ? $this->updateAndUpload('module/', $module->thumbnail, 'png', $request->file('thumbnail')) : $module->thumbnail,
            'theme_id' => 1,
            'description' => $request->description[array_search('default', $request->lang)],
            'all_zone_service' => false,
        ];
    }

    public function getDropdownData(Object $data, object $request): array
    {

        $formattedData = $data->map(function ($condition) {
            return [
                'id' => $condition->id,
                'text' => $condition->name,
            ];
        });


        if(isset($request->all))
        {
            $formattedData[]=(object)['id'=>'all', 'text'=>translate('messages.all')];
        }

        return $formattedData;
    }

}
