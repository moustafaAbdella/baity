<?php

return [
    'module_type'=>[
        'grocery', 'food', 'pharmacy', 'ecommerce','parcel'
    ],
    'module_type_authority_id'=>[
        'Nap4gA1tyeY=', 'NV25GlPuOnQ=', 'w+mTCW1569Y=', 'oIcaYzeDfQQ=','rAy9UhMUw6Y='
    ],

    'grocery'=>[
        'order_status'=>['accepted'=>false],
        'order_place_to_schedule_interval'=>true,
        'add_on'=>false,
        'stock'=>true,
        'veg_non_veg'=>false,
        'unit'=>true,
        'order_attachment'=>false,
        'always_open'=>false,
        'all_zone_service'=>false,
        'item_available_time'=>false,
        'show_restaurant_text'=>false,
        'is_parcel'=>false,
        'organic'=>true,
        'cutlery'=>false,
        'common_condition'=>false,
        'nutrition'=>true,
        'allergy'=>true,
        'basic'=>false,
        'halal'=>true,
        'brand'=>false,
        'generic_name'=>false,
        'description'=>'In this type, You can set delivery slot start after x minutes from current time, No available time for items and has stock for items.',
    ],

    'food'=>[
        'order_status'=>['accepted'=>true],
        'order_place_to_schedule_interval'=>false,
        'add_on'=>true,
        'stock'=>false,
        'veg_non_veg'=>true,
        'unit'=>false,
        'order_attachment'=>false,
        'always_open'=>false,
        'all_zone_service'=>false,
        'item_available_time'=>true,
        'show_restaurant_text'=>true,
        'is_parcel'=>false,
        'organic'=>false,
        'cutlery'=>true,
        'common_condition'=>false,
        'nutrition'=>true,
        'allergy'=>true,
        'basic'=>false,
        'halal'=>true,
        'brand'=>false,
        'generic_name'=>false,
        'description'=>'In this type, you can set item available time, no stock management for items and has option to add add-on.',
    ],

    'pharmacy'=>[
        'order_status'=>['accepted'=>false],
        'order_place_to_schedule_interval'=>false,
        'add_on'=>false,
        'stock'=>true,
        'veg_non_veg'=>false,
        'unit'=>true,
        'order_attachment'=>true,
        'always_open'=>false,
        'all_zone_service'=>false,
        'item_available_time'=>false,
        'show_restaurant_text'=>false,
        'is_parcel'=>false,
        'organic'=>false,
        'cutlery'=>false,
        'common_condition'=>true,
        'nutrition'=>false,
        'allergy'=>false,
        'basic'=>true,
        'halal'=>false,
        'brand'=>false,
        'generic_name'=>true,
        'description'=>'In this type, Customer can upload prescription when place order, No available time for items and has stock for items.',
    ],

    'ecommerce'=>[
        'order_status'=>['accepted'=>false],
        'order_place_to_schedule_interval'=>false,
        'add_on'=>false,
        'stock'=>true,
        'veg_non_veg'=>false,
        'unit'=>true,
        'order_attachment'=>false,
        'always_open'=>true,
        'all_zone_service'=>true,
        'item_available_time'=>false,
        'show_restaurant_text'=>false,
        'is_parcel'=>false,
        'organic'=>false,
        'cutlery'=>false,
        'common_condition'=>false,
        'nutrition'=>false,
        'allergy'=>false,
        'basic'=>false,
        'halal'=>false,
        'brand'=>true,
        'generic_name'=>false,
        'description'=>'In this type, No opening and closing time for store, no available time for items and has stock for items.',
    ],

    'parcel'=>[
        'order_status'=>['accepted'=>false],
        'order_place_to_schedule_interval'=>false,
        'add_on'=>false,
        'stock'=>false,
        'veg_non_veg'=>false,
        'unit'=>false,
        'order_attachment'=>false,
        'always_open'=>true,
        'all_zone_service'=>false,
        'item_available_time'=>false,
        'show_restaurant_text'=>false,
        'is_parcel'=>true,
        'organic'=>false,
        'cutlery'=>false,
        'common_condition'=>false,
        'nutrition'=>false,
        'allergy'=>false,
        'basic'=>false,
        'halal'=>false,
        'brand'=>false,
        'generic_name'=>false,
        'description'=>'',
    ],
];
