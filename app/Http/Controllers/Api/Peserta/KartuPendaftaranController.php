<?php
namespace App\Http\Controllers\Api\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Archive;
use App\Models\NilaiSemester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KartuPendaftaranController extends Controller
{
    protected function __create_roundimg($source, $radius)
    {
        $ws = imagesx($source);
        $hs = imagesy($source);

        $corner = $radius + 2;
        $s = $corner*2;

        $src = imagecreatetruecolor($s, $s);
        imagecopy($src, $source, 0, 0, 0, 0, $corner, $corner);
        imagecopy($src, $source, $corner, 0, $ws - $corner, 0, $corner, $corner);
        imagecopy($src, $source, $corner, $corner, $ws - $corner, $hs - $corner, $corner, $corner);
        imagecopy($src, $source, 0, $corner, 0, $hs - $corner, $corner, $corner);

        $q = 4; # change this if you want
        $radius *= $q;

        # find unique color
        do {
            $r = rand(0, 255);
            $g = rand(0, 255);
            $b = rand(0, 255);
        } while (imagecolorexact($src, $r, $g, $b) < 0);

        $ns = $s * $q;

        $img = imagecreatetruecolor($ns, $ns);
        $alphacolor = imagecolorallocatealpha($img, $r, $g, $b, 127);
        imagealphablending($img, false);
        imagefilledrectangle($img, 0, 0, $ns, $ns, $alphacolor);

        imagefill($img, 0, 0, $alphacolor);
        imagecopyresampled($img, $src, 0, 0, 0, 0, $ns, $ns, $s, $s);
        imagedestroy($src);

        imagearc($img, $radius - 1, $radius - 1, $radius * 2, $radius * 2, 180, 270, $alphacolor);
        imagefilltoborder($img, 0, 0, $alphacolor, $alphacolor);
        imagearc($img, $ns - $radius, $radius - 1, $radius * 2, $radius * 2, 270, 0, $alphacolor);
        imagefilltoborder($img, $ns - 1, 0, $alphacolor, $alphacolor);
        imagearc($img, $radius - 1, $ns - $radius, $radius * 2, $radius * 2, 90, 180, $alphacolor);
        imagefilltoborder($img, 0, $ns - 1, $alphacolor, $alphacolor);
        imagearc($img, $ns - $radius, $ns - $radius, $radius * 2, $radius * 2, 0, 90, $alphacolor);
        imagefilltoborder($img, $ns - 1, $ns - 1, $alphacolor, $alphacolor);
        imagealphablending($img, true);
        imagecolortransparent($img, $alphacolor);

        # resize image down
        $dest = imagecreatetruecolor($s, $s);
        imagealphablending($dest, false);
        imagefilledrectangle($dest, 0, 0, $s, $s, $alphacolor);
        imagecopyresampled($dest, $img, 0, 0, 0, 0, $s, $s, $ns, $ns);
        imagedestroy($img);

        # output image
        imagealphablending($source, false);
        imagecopy($source, $dest, 0, 0, 0, 0, $corner, $corner);
        imagecopy($source, $dest, $ws - $corner, 0, $corner, 0, $corner, $corner);
        imagecopy($source, $dest, $ws - $corner, $hs - $corner, $corner, $corner, $corner, $corner);
        imagecopy($source, $dest, 0, $hs - $corner, 0, $corner, $corner, $corner);
        imagealphablending($source, true);
        imagedestroy($dest);

        return $source;
    }

    public function show(Request $request) {
        $user = auth('archive')->user();
        if (!$user->photo_path && !$user->skhu_path) {
            return response('fail to generate', 400);
        }

        $zeroPoints = NilaiSemester::where('archive_id', $user->id)->where(function ($query) {
            return $query->where('s1', 0)->orWhere('s2', 0)->orWhere('s3', 0)->orWhere('s4', 0)->orWhere('s5', 0);
        });

        if ($zeroPoints->exists()) {
            return response('fail to generate', 401);
        }
    
        $kartu_path = Storage::path('public/cards/kartu_' . $user->id . '.png');

        if (Storage::fileExists($kartu_path) && $request->query('force') === null) {
            return response()->file($kartu_path);
        } else {
            $p = Storage::path($user->photo_path);
            $profile_image = null;

            switch(pathinfo($p, PATHINFO_EXTENSION)) {
                case 'png':
                    $profile_image = imagecreatefrompng($p);
                    break;
                case 'jpeg':
                case 'jpg':
                default:
                    $profile_image = imagecreatefromjpeg($p);
                    break;
            }

            $no = Archive::all()->search(function(mixed $item) {
                if ($item->id === auth('archive')->user()->id) {
                    return $item;
                }
            });

            if ($no === null) {
                return response('fail to generate', 400);
            }
        
            $base_image = imagecreatefrompng(Storage::path('base.png'));
            $font_file = Storage::path('poppins.ttf');

            $profile_image = imagescale($profile_image, 171, 180);
            $profile_image = $this->__create_roundimg($profile_image, 25);

            imagecopy($base_image, $profile_image, 290, 175, 0, 0, imagesx($profile_image), imagesy($profile_image));
            
            imagettftext($base_image, 20, 0, 300, 424, 0, $font_file, $user->name);
            imagettftext($base_image, 20, 0, 300, 492, 0, $font_file, 'PSB_' . str_pad(strval($no+1), 3, '0', STR_PAD_LEFT));
            imagettftext($base_image, 20, 0, 300, 560, 0, $font_file, '0' . strval($user->phone));
            imagettftext($base_image, 20, 0, 300, 630, 0, $font_file, ucwords($user->type));
            imagettftext($base_image, 20, 0, 300, 696, 0, $font_file, strtoupper($user->school));

            if (!imagepng($base_image, $kartu_path)) {
                return response('fail to generate', 400);
            }

            return response()->file($kartu_path);
        }
    }
}