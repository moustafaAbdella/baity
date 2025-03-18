<?php

namespace App\Http\Controllers;

use App\Models\DeliveryMan;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\Admin;
use App\Models\BusinessSetting;
use Gregwar\Captcha\CaptchaBuilder;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class DeliveryManController extends Controller
{

    public function create()
    {
        $status = BusinessSetting::where('key', 'toggle_dm_registration')->first();
        if (!isset($status) || $status->value == '0') {
            Toastr::error(translate('messages.not_found'));
            return back();
        }

        $custome_recaptcha = new CaptchaBuilder;
        $custome_recaptcha->build();
        $regions = $this->getRegions();
        $identityTypes = $this->getIdentityTypes();
        Session::put('six_captcha', $custome_recaptcha->getPhrase());

        return view('dm-registration', compact('custome_recaptcha', 'regions' ,'identityTypes'));
    }

    public function store(Request $request)
    {
        $status = BusinessSetting::where('key', 'toggle_dm_registration')->first();
        if (!isset($status) || $status->value == '0') {
            Toastr::error(translate('messages.not_found'));
            return back();
        }

        $recaptcha = Helpers::get_business_settings('recaptcha');
        if (isset($recaptcha) && $recaptcha['status'] == 1) {
            $request->validate([
                'g-recaptcha-response' => [
                    function ($attribute, $value, $fail) {
                        $secret_key = Helpers::get_business_settings('recaptcha')['secret_key'];
                        $gResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                            'secret' => $secret_key,
                            'response' => $value,
                            'remoteip' => \request()->ip(),
                        ]);

                        if (!$gResponse->successful()) {
                            $fail(translate('ReCaptcha Failed'));
                        }
                    },
                ],
            ]);
        } else if (session('six_captcha') != $request->custome_recaptcha) {
            Toastr::error(trans('messages.ReCAPTCHA Failed'));
            return back();
        }

        // استدعاء دالة createdriver
        $Driver = $this->createdriver($request);


        // التحقق من قيمة $Driver
        $responsedata = json_decode($Driver->getContent(), true);
        if (isset($responsedata['status']) && $responsedata['status'] === true) {

            // استكمال العملية إذا كانت قيمة $Driver صحيحة
            $request->validate([
                'f_name' => 'required|max:100',
                'l_name' => 'nullable|max:100',
                'identity_number' => 'required|max:30',
                'email' => 'required|unique:delivery_men',
                'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:delivery_men',
                'zone_id' => 'required',
                'vehicle_id' => 'required',
                'earning' => 'required',
                'password' => ['required', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            ], [
                'f_name.required' => translate('messages.first_name_is_required'),
                'zone_id.required' => translate('messages.select_a_zone'),
                'vehicle_id.required' => translate('messages.select_a_vehicle'),
                'earning.required' => translate('messages.select_dm_type')
            ]);

            if ($request->has('image')) {
                $image_name = Helpers::upload('delivery-man/', 'png', $request->file('image'));
            } else {
                $image_name = 'def.png';
            }

            $id_img_names = [];
            if (!empty($request->file('identity_image'))) {
                foreach ($request->identity_image as $img) {
                    $identity_image = Helpers::upload('delivery-man/', 'png', $img);
                    array_push($id_img_names, ['img' => $identity_image, 'storage' => Helpers::getDisk()]);
                }
                $identity_image = json_encode($id_img_names);
            } else {
                $identity_image = json_encode([]);
            }

            // إضافة السائق الجديد
            $dm = new DeliveryMan();
            $dm->f_name = $request->f_name;
            $dm->l_name = $request->l_name;
            $dm->email = $request->email;
            $dm->phone = $request->phone;
            $dm->identity_number = $request->identity_number;
            $dm->identity_type = $request->identity_type;
            $dm->vehicle_id = $request->vehicle_id;
            $dm->zone_id = $request->zone_id;
            $dm->identity_image = $identity_image;
            $dm->image = $image_name;
            $dm->active = 0;
            $dm->earning = $request->earning;
            $dm->password = bcrypt($request->password);
            $dm->application_status = 'pending';
            $dm->idNumber = $request->idNumber;
            $dm->save();

            // إرسال البريد الإلكتروني للمسجلين
            try {
                $admin = Admin::where('role_id', 1)->first();

                if (config('mail.status') && Helpers::get_mail_status('registration_mail_status_dm') == '1' && Helpers::getNotificationStatusData('deliveryman', 'deliveryman_registration', 'mail_status')) {
                    Mail::to($request->email)->send(new \App\Mail\DmSelfRegistration('pending', $dm->f_name . ' ' . $dm->l_name));
                }
                if (config('mail.status') && Helpers::get_mail_status('dm_registration_mail_status_admin') == '1' && Helpers::getNotificationStatusData('admin', 'deliveryman_self_registration', 'mail_status')) {
                    Mail::to($admin['email'])->send(new \App\Mail\DmRegistration('pending', $dm->f_name . ' ' . $dm->l_name));
                }
            } catch (\Exception $ex) {
                info($ex->getMessage());
            }
            Toastr::success(translate('messages.application_placed_successfully'));
            return back();
        } else {

            $responseData = json_decode($Driver->getContent(), true);
            $errorCode = $responseData['errorCodes'];
            $errorCode = implode(', ', $errorCode);
            $finalErrorMessage = __('messages.error_' . $errorCode, ['default' => 'Unknown error']);

            Toastr::error('Authority Registration Failed. ' . $finalErrorMessage, 'Error');


            return back();
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


    public function getIdentityTypes()
    {
        // استدعاء الـ API باستخدام Guzzle أو HTTP Client في Laravel
        $response = Http::post('https://demo-apitawseel.naql.sa/api/Lookup/identity-types-list', [
            'companyName' => 'master-taer',
            'password' => 'FRgQKCtIDClc'
        ]);

        // تحقق من الاستجابة
        if ($response->successful()) {
            $regions = $response->json()['data']; // استخراج البيانات
            return $regions;
        } else {
            return response()->json(['error' => 'Failed IdentityTypes'], 500);
        }
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

    public function createdriver(Request $request)
    {

        $request->validate([
         //authority validate
         'identityTypeId' => 'required',
         'idNumber' => 'required',
         'mobile' => 'required',
         'dateOfBirth' => 'required',
         'vehicle_id' => 'required',
         'carNumber' => 'required',
         'vehicleSequenceNumber' => 'required',
         'regionId' => 'required',
         'cityId' => 'required',
         //####################
        ]);
        $DateOfBirth= Carbon::parse($request->dateOfBirth)->format('Ymd');

        $vehicle = \App\Models\DMVehicle::where('id', $request->vehicle_id)->first();
        $formattedDate = Carbon::now()->format('Y-m-d\TH:i:s') . '.277Z';


        $response = Http::post('https://demo-apitawseel.naql.sa/api/Driver/create', [
           'credential' => [
                'companyName' => 'master-taer',
                'password' => 'FRgQKCtIDClc',
            ],

            'driver'=> [
                'identityTypeId' => $request->identityTypeId,
                'idNumber' => $request->idNumber,
                'dateOfBirth' => $DateOfBirth,
                'registrationDate' => $formattedDate,
                'mobile' => $request->mobile,
                'regionId' => $request->regionId,
                'carTypeId' => $vehicle->authority_id,
                'cityId' => $request->cityId,
                'carNumber' => $request->carNumber,
                'vehicleSequenceNumber' => $request->vehicleSequenceNumber,
            ]

        ]);

        // تحقق من الاستجابة
        if ($response->successful()) {
            // الوصول إلى كلا الحقلين 'status' و 'errorCodes' من استجابة الـ API
            $status = $response->json()['status'];
            $errorCodes = $response->json()['errorCodes'] ?? 'No error code provided';  // استخدم قيمة افتراضية إذا كانت 'errorCodes' غير موجودة


            return response()->json([
                'status' => $status,
                'errorCodes' => $errorCodes
            ]);
        } else {
            return response()->json(['error' => 'Failed to fetch authority'], 500);
        }
    }


}
