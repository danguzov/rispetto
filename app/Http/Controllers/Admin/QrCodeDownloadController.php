<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\RispettoQrCode;
use Illuminate\Http\Request;

class QrCodeDownloadController extends Controller
{
    public function __invoke(Request $request, string $type)
    {
        abort_unless(in_array($type, [
            RispettoQrCode::TYPE_MENU,
            RispettoQrCode::TYPE_INSTAGRAM,
            RispettoQrCode::TYPE_FACEBOOK,
        ], true), 404);

        return RispettoQrCode::downloadResponse($type);
    }
}
