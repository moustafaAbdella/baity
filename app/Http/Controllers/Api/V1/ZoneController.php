<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Zone;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\CentralLogics\Helpers;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Support\Facades\Validator;
class ZoneController extends Controller
{
    public function get_zones()
    {
        $zones= Zone::where('status',1)->get();
        foreach($zones as $zone){
            $area = json_decode($zone->coordinates[0]->toJson(),true);
            $zone['formated_coordinates']=Helpers::format_coordiantes($area['coordinates']);
        }
        return response()->json($zones, 200);
    }

    public function zonesCheck(Request $request){
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
            'zone_id' => 'required',
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $zone = Zone::where('id',$request->zone_id)->whereContains('coordinates', new Point($request->lat, $request->lng, POINT_SRID))->exists();

        return response()->json($zone, 200);

    }

    public function getRegions()
    {
        // استدعاء الـ API باستخدام Guzzle أو HTTP Client في Laravel
        $response = Http::post('https://demo-apitawseel.naql.sa/api/Lookup/regions-list', [
            'companyName' => 'master-taer',
            'password' => 'FRgQKCtIDClc'
        ]);

        // تحقق من الاستجابة
        if ($response->successful()) {
            $regions = $response->json()['data'];
            return response()->json($regions, 200);
        } else {
            return response()->json(['error' => 'Failed to fetch regions'], 500);
        }
    }

    public function getCities($regionId)
    {
        $body = json_encode([
            'credential' => [
                'companyName' => 'master-taer',
                'password' => 'FRgQKCtIDClc',
            ],
            'regionId' => $regionId,
        ]);
        // إرسال الطلب للـ API للمدن
        $response = Http::post('https://demo-apitawseel.naql.sa/api/Lookup/cities-list',[
            'credential' => [
                'companyName' => 'master-taer',
                'password' => 'FRgQKCtIDClc',
            ],
            'regionId' => $regionId,
        ]);

        // تحقق من نجاح الطلب
        if ($response->successful()) {
            $cities = $response->json()['data']; // استخراج البيانات
            return  $cities;
        } else {
            return response()->json(['error' => 'Failed to fetch Cities'], 500);
        }
    }

    public function getcarTypes()
    {
        $response = Http::post('https://demo-apitawseel.naql.sa/api/Lookup/car-types-list', [
            'companyName' => 'master-taer',
            'password' => 'FRgQKCtIDClc'
        ]);

        if ($response->successful()) {
            $regions = $response->json()['data']; 
            return $regions;
        } else {
            return response()->json(['error' => 'Failed IdentityTypes'], 500);
        }
    }

}
