<?php

namespace App\Services;

use Throwable;
use ImagickPixel;
use App\Contracts\Repositories\AgencyRepository;
use App\Contracts\Repositories\AreaAgencyRepository;
use App\Contracts\Repositories\CardRepository;
use App\Contracts\Repositories\DesignRepository;
use App\Contracts\Repositories\ImageRepository;
use App\Models\AreaAgency;
use App\Models\Card;
use App\Models\Design;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\SampleText;
use GuzzleHttp\Client;
use Intervention\Image\Laravel\Facades\Image;
use Imagick;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Hashids\Hashids;
use App\Models\Image as ImgModel;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;

class CardService
{
    protected $client;

    protected $kumihanService;

    protected $imageRepository;

    protected $model;

    protected $MM_TO_PX = 25.4;

    protected $designService;

    protected $designRepository;

    protected $agencyRepository;

    protected $areaAgencyRepository;

    protected $cardRepository;

    protected $vDesignMService;

    public function __construct(
        Card $model,
        Client $client,
        KumihanService $kumihanService,
        ImageRepository $imageRepository,
        DesignService $designService,
        DesignRepository $designRepository,
        AgencyRepository $agencyRepository,
        AreaAgencyRepository $areaAgencyRepository,
        CardRepository $cardRepository,
        VDesignMService $vDesignMService,
    ) {
        $this->client = $client;
        $this->model = $model;
        $this->kumihanService = $kumihanService;
        $this->imageRepository = $imageRepository;
        $this->designService = $designService;
        $this->designRepository = $designRepository;
        $this->agencyRepository = $agencyRepository;
        $this->areaAgencyRepository = $areaAgencyRepository;
        $this->cardRepository = $cardRepository;
        $this->vDesignMService = $vDesignMService;
    }

    public function findCardByHashId($hashid)
    {
        $hashids = new Hashids(config('common.hashids.salt'), config('common.hashids.length'));

        $unHashCardId = $hashids->decode($hashid);
        //dd($hashids->encode([0 => 8435]));
        //WPBMrJkXA3G2YxoEjynqZy9Lp5vDOeQz
        //        return $this->model->findOrFail($hashid);
        return $this->model->findOrFail($unHashCardId[0] ?? 0)->load('vDesign');
    }

    public function findCardByKumihanId($kumihanId, $throwException = true)
    {
        if (!$throwException) {
            return $this->model->where('kumihan_id', $kumihanId)->first();
        }

        return $this->model->where('kumihan_id', $kumihanId)->firstOrFail();
    }

    public function getCardsByKumihanIds($kumihanIds)
    {
        return $this->model->with(['receivers'])->whereIn('kumihan_id', $kumihanIds)->get();
    }

    public function create($input)
    {
        DB::beginTransaction();

        try {
            // Gen aid and bcsid prepare insert to card
            [$oid, $aid] = get_cart_card_atena();
            $dmInfo = $input['dm_info']['other_info'] ?? null;
            // Check is call dm
            if ($dmInfo) {
                $atenaFlg = $dmInfo['atena_flg'] ?? 0;
                $atenaAid = $dmInfo['atena_aid'] ?? null;
                $atenaOid = $dmInfo['atena_oid'] ?? null;
                if ($atenaFlg && $atenaAid && $atenaOid) {
                    $aid = $atenaAid;
                }
            }
            // Update card atena aid and bcsid
            $input['aid'] = $aid;
            $input['bcsid'] = $oid;

            $card = $this->model->create($input);
            if (isset($input['elements'])) {
                $card->elements()->createMany($input['elements']);
            }

            $card->kumihan_id = generateKumihanId($card);
            $card->save();

            DB::commit();

            return $card;
        } catch (Throwable $e) {
            DB::rollback();
            Log::error($e);

            return false;
        }
    }

    public function getSampleTexts()
    {
        $categories = app(Category::class)->all();
        $sampleTexts = [];

        if ($categories->count()) {
            $sampleTexts = $this->getSampleTextByCategoryId($categories[0]->id);
        }

        return compact('categories', 'sampleTexts');
    }

    public function getSampleTextsMourning()
    {
        $categories = app(Category::class)->all();
        $sampleTexts = [];

        if ($categories->count()) {
            $sampleTexts = SampleText::where('type', config('card.type_sample_text.mourning'))
                ->where('category_id', $categories[0]->id)
                ->get();
        }

        return compact('categories', 'sampleTexts');
    }

    public function getSampleTextsKanchuu()
    {
        $categories = app(Category::class)->all();
        $sampleTexts = [];

        if ($categories->count()) {
            $sampleTexts = SampleText::where('type', config('card.type_sample_text.kanchuu'))
                ->where('category_id', $categories[0]->id)
                ->get();
        }

        return compact('categories', 'sampleTexts');
    }

    public function getSampleTextByCategoryId($categoryId)
    {
        return SampleText::where('category_id', $categoryId)
            ->where('type', config('card.type_sample_text.new_year'))
            ->get();
    }

    public function getSampleTextByValue($value)
    {
        $valueRegex = preg_replace("/\r\n/", "\\r ", $value);
        return DB::select("SELECT * FROM sample_texts where `value` = '$valueRegex' or `value` = '$value'");
    }

    public function update($input, Card $card)
    {
        $cardData = [
            'last_name' => $input['last_name'] ?? null,
            'first_name' => $input['first_name'] ?? null,
            'last_name_furigana' => $input['last_name_furigana'] ?? null,
            'first_name_furigana' => $input['first_name_furigana'] ?? null,
            'furigana_old_last_name_age' => $input['furigana_old_last_name_age'] ?? null,
            'furigana_title' => $input['furigana_title'] ?? null,
            'email_url' => $input['email_url'] ?? [],
            'layout_code' => $input['layout_code'] ?? null,
            'style' => array_merge($card->style, $input['style'] ?? []),
            'is_pdf_created' => $this->model::IS_NOT_PDF_CREATED,
            'upload_photo_complete' => $input['upload_photo_complete'] ?? 0,
            'image_after_upload' => $input['image_after_upload'] ?? null,
            'user_detail_id' => $input['user_detail_id'] ?? null,
            'detail_mode' => $input['detail_mode'] ?? 0,
            'moved_frames' => $input['moved_frames'] ?? [],
            'mei_1_soroe' => $input['mei_1_soroe'] ?? null,
        ];

        $cardData['style']['kumihan_count'] = $card->style['kumihan_count'];
        $cardData['style']['isTypeSetting'] = !empty($input['user_detail_id']) || !empty($card->dm_info) || !empty($card->style['isTypeSetting']);
        // $cardData['style']['isTypeSetting'] = !empty($card->user_detail_id) || session()->has('dm_info');
        $card->update($cardData);

        $card->addresses()->delete();
        if (isset($input['addresses'])) {
            $card->addresses()->createMany($input['addresses']);
        }

        $card->participants()->delete();
        if (isset($input['participants'])) {
            $card->participants()->createMany($input['participants']);
        }

        if (isset($input['company']) && $input['company'] && $card->isAllowCorporateUse()) {
            $input['company']['small_text'] = $input['company']['small_text'] ?? 0;
            if ($card->company) {
                $card->company->update($input['company']);
            } else {
                $card->company()->create($input['company']);
            }
        } else {
            if ($card->company) {
                $card->company->delete();
            }
        }

        if (isset($input['user_photo'])) {
            $card->elements()
                ->where('style->type', config('card.element_type.image'))
                ->delete();
            $card->elements()->createMany($input['user_photo']);
        }

        return $card;
    }

    public function getImageParametersFromECCube()
    {
        $data = [
            'background' => [
                'url' => asset('images/background.jpg'),
                'width' => 100,
                'height' => 148,
            ],
            'text' => '皆様のご健康とご多幸をお祈りいたします 本年もどうぞよろしくお願い申し上げます 令和二年　元旦',
            'frames' => [
                [
                    'width' => 24.465,
                    'height' => 34.78,
                    'x_coordinate' => 22.095,
                    'y_coordinate' => 50.53,
                ],
                [
                    'width' => 27.655,
                    'height' => 17.99,
                    'x_coordinate' => 56.495,
                    'y_coordinate' => 51.06,
                ],
                [
                    'width' => 28.027,
                    'height' => 18.19,
                    'x_coordinate' => 18.289,
                    'y_coordinate' => 96.014,
                ],
                [
                    'width' => 23.539,
                    'height' => 33.698,
                    'x_coordinate' => 56.986,
                    'y_coordinate' => 78.911,
                ],
            ],
        ];

        return json_encode($data);
    }

    public function uploadCrops($input, $card)
    {
        try {
            $hashId = $card->hash_id;
            $cardSession = session()->get("card_session_$hashId", $card->toArray());
            if (!empty($input['crops'])) {
                $crops = $input['crops'];
                foreach ($crops as $index => $crop) {
                    if ($crop['style']['type'] === config('card.element_type.crop')) {
                        $uploadDir = self::getCardFolder($card)
                            . $crop['style']['crop_id'];
                        if (!File::isDirectory($uploadDir)) {
                            File::makeDirectory($uploadDir, 0777, true, true);
                        }

                        if (substr($crop['style']['uploadImageData']['source'], 0, 10) === 'data:image') {
                            $uploadImg = Image::read($crop['style']['uploadImageData']['source']);
                            $imagick = $uploadImg->core()->native();
                            $colorSpaceCode = $imagick->getImageColorspace();
                            if ($colorSpaceCode !== Imagick::COLORSPACE_RGB
                                && $colorSpaceCode !== Imagick::COLORSPACE_SRGB) {
                                return [
                                    'status' => 'NOT_RGB',
                                    'message' => $this->getColorSpaceName($colorSpaceCode),
                                ];
                            }

                            $uploadImageName = sprintf(
                                config('card.user_photo_name'),
                                sha1(time()),
                                'upload-' . $crop['style']['crop_id'],
                                'png',
                            );
                            $uploadImg->save($uploadDir . '/' . $uploadImageName);

                            $crop['style']['uploadImageData']['source'] = self::getCardFolder($card, true)
                                . $crop['style']['crop_id'] . '/' . $uploadImageName;
                        }

                        if (substr($crop['image'], 0, 10) === 'data:image') {
                            $cropImg = Image::read($crop['image']);
                            $cropImageName = sprintf(
                                config('card.user_photo_name'),
                                sha1(time()),
                                $crop['style']['crop_id'],
                                'png',
                            );
                            $cropImg->save($uploadDir . '/' . $cropImageName);

                            $crop['image'] = self::getCardFolder($card, true)
                                . $crop['style']['crop_id'] . '/' . $cropImageName;
                        }
                    }
                    $crops[$index] = $crop;
                }
                $cardSession['crop_photo'] = $crops;
            } else {
                $cardSession['crop_photo'] = [];
            }
            session(["card_session_$hashId" => $cardSession]);

            return [
                'status' => 'OK',
                'message' => 'success',
            ];
        } catch (Exception $e) {
            Log::error($e);

            return [
                'status' => 'ERROR',
                'message' => 'something went wrong',
            ];
        }
    }

    public function uploadImages($input, $card)
    {
        try {
            $hashId = $card->hash_id;
            $sessionId = get_session_id();

            $uploadDir = self::getCardFolder($card) . $sessionId;
            $card->load([
                'addresses',
                'participants',
                'company',
                'elements',
            ]);
            $cardSession = session()->get("card_session_$hashId", $card->toArray());
            // save list of uploading images
            foreach ($input['images'] as &$image) {
                if (filter_var($image['src'], FILTER_VALIDATE_URL)) {
                    $imageName = basename(parse_url($image['src'], PHP_URL_PATH));
                } else {
                    $img = Image::read($image['src']);
                    $imageName = sprintf(
                        config('card.user_photo_name'),
                        sha1(time()),
                        sprintf('%02s', $image['style']['mask_id']),
                        $image['extension'] ?? explode('/', $img->mediaType())[1],
                    );
                    $srcFile = $uploadDir . '/' . $imageName;
                    $imagick = $img->core()->native();
                    $colorSpaceCode = $imagick->getImageColorspace();
                    if ($colorSpaceCode !== Imagick::COLORSPACE_RGB
                        && $colorSpaceCode !== Imagick::COLORSPACE_SRGB) {
                        return [
                            'status' => 'NOT_RGB',
                            'message' => $this->getColorSpaceName($colorSpaceCode),
                        ];
                    }
                    Storage::disk(config('card.driver'))->put($srcFile, (string) $img->encode());
                }

                $image['image'] = $imageName;
                unset($image['src']);
            }

            $cardSession['user_photo'] = $input['images'];

            // save complete upload design
            $uploadCard = Image::read($input['exportImage']);
            $designName = config('card.image_name.edit_design') . '.jpg';
            $srcFile = $uploadDir . '/' . $designName;
            Storage::disk(config('card.driver'))->put($srcFile, (string) $uploadCard->encode());

            $cardSession['upload_photo_complete'] = config('card.upload_photo_complete');
            $cardSession['image_after_upload'] = $designName;
            $cardSession['is_changed_photo'] = $input['isChangedPhoto'];
            session(["card_session_$hashId" => $cardSession]);
            return [
                'status' => 'OK',
                'message' => 'success',
            ];
        } catch (Exception $e) {
            Log::error($e);

            return [
                'status' => 'ERROR',
                'message' => 'something went wrong',
            ];
        }
    }

    public function getColorSpaceName($code)
    {
        if ($code === Imagick::COLORSPACE_GRAY) {
            return 'GRAY';
        }

        if ($code === Imagick::COLORSPACE_TRANSPARENT) {
            return 'TRANSPARENT';
        }

        if ($code === Imagick::COLORSPACE_OHTA) {
            return 'OHTA';
        }

        if ($code === Imagick::COLORSPACE_LAB) {
            return 'LAB';
        }

        if ($code === Imagick::COLORSPACE_XYZ) {
            return 'XYZ';
        }

        if ($code === Imagick::COLORSPACE_YCBCR) {
            return 'YCBCR';
        }

        if ($code === Imagick::COLORSPACE_YCC) {
            return 'YCC';
        }

        if ($code === Imagick::COLORSPACE_YIQ) {
            return 'YIQ';
        }

        if ($code === Imagick::COLORSPACE_YPBPR) {
            return 'YPBPR';
        }

        if ($code === Imagick::COLORSPACE_YUV) {
            return 'YUV';
        }


        if ($code === Imagick::COLORSPACE_CMYK) {
            return 'CMYK';
        }

        if ($code === Imagick::COLORSPACE_HSB) {
            return 'HSB';
        }

        if ($code === Imagick::COLORSPACE_HSL) {
            return 'HSL';
        }

        if ($code === Imagick::COLORSPACE_HWB) {
            return 'HWB';
        }

        if ($code === Imagick::COLORSPACE_REC601LUMA) {
            return 'REC601LUMA';
        }

        if ($code === Imagick::COLORSPACE_REC709LUMA) {
            return 'REC709LUMA';
        }

        if ($code === Imagick::COLORSPACE_LOG) {
            return 'LOG';
        }

        if ($code === Imagick::COLORSPACE_RGB) {
            return 'RGB';
        }

        if ($code === Imagick::COLORSPACE_SRGB) {
            return 'SRGB';
        }

        return 'UNDEFINED';
    }

    public function exportPreview($id, $kumihanPath = '')
    {
        try {
            $card = $this->findCardById($id);
            $cardFolder = self::getCardFolder($card);
            $previewName = config('card.image_name.preview') . '.jpg';
            $jpgPath = $cardFolder . $previewName;
            if ($card->has_atena && File::exists($jpgPath)) {
                return true;
            }
            $sessionId = get_session_id();
            $cardSession = $this->getCardPreviewSession($card);
            $cardPreviewDpi = config('card.preview_dpi');
            $cardWidth = $card->style['width'] * $cardPreviewDpi / 25.4;
            $cardHeight = $card->style['height'] * $cardPreviewDpi / 25.4;
            $final = $this->createPreviewCanvas($cardWidth, $cardHeight);
            $clips = $card->getClipElements()->keyBy('id')->toArray();
            $userPhotos = $this->getCardPreviewUserPhotos($card, $cardSession);
            $this->composePreviewUserPhotos(
                $final,
                $userPhotos,
                $clips,
                $cardFolder,
                $sessionId,
                $kumihanPath,
                $cardPreviewDpi,
                $cardWidth,
                $cardHeight,
            );

            $background = $this->getPreviewBackground($card, $cardFolder, $sessionId, $cardWidth, $cardHeight);
            $backgroundBitDepth = $background->getImageDepth();

            $complete = $this->getPreviewCompleteImage($card, $sessionId, $kumihanPath, $cardWidth, $cardHeight);

            $final->compositeImage($background, Imagick::COMPOSITE_OVER, 0, 0);
            $stampAndCropElements = $this->getSortedStampAndCropElements($cardSession);
            $this->composeStampAndCropElements($final, $stampAndCropElements, $cardPreviewDpi);

            if ($complete !== null) {
                $final->compositeImage($complete, Imagick::COMPOSITE_OVER, 0, 0);
            }
            $this->storePreviewImage($final, $jpgPath, $cardPreviewDpi, $backgroundBitDepth);
            $this->updatePreviewImageState($card, $previewName);

            return true;
        } catch (Exception $e) {
            Log::error($e);

            return false;
        }
    }

    public function exportAtenaPreview($id, $kumihanPath = '')
    {
        try {
            $card = $this->findCardById($id);
            if (!$card->has_atena) {
                return true;
            }
            $cardFolder = self::getCardFolder($card);
            $sessionId = get_session_id();
            $cardPreviewDpi = config('card.preview_dpi');
            $cardWidth = 100 * $cardPreviewDpi / 25.4;
            $cardHeight = 148 * $cardPreviewDpi / 25.4;
            $final = $this->createPreviewCanvas($cardWidth, $cardHeight);

            $backgroundPath = config('card.atena_preview_path');
            $previewName = config('card.image_name.atena_preview');
            $jpgPath = $cardFolder . $previewName;

            $background = Image::read($backgroundPath)->core()->native();
            $background->scaleImage($cardWidth, $cardHeight);
            $backgroundBitDepth = $background->getImageDepth();
            $complete = $this->getAtenaPreviewCompleteImage($cardFolder, $sessionId, $kumihanPath, $cardWidth, $cardHeight);
            $final->compositeImage($background, Imagick::COMPOSITE_OVER, 0, 0);
            $final->compositeImage($complete, Imagick::COMPOSITE_OVER, 0, 0);
            $this->storePreviewImage($final, $jpgPath, $cardPreviewDpi, $backgroundBitDepth);
            $this->updatePreviewImageState($card, config('card.image_name.preview') . '.jpg');

            return true;
        } catch (Exception $e) {
            Log::error($e);

            return false;
        }
    }

    private function getCardPreviewSession(Card $card)
    {
        $hashid = $card->hash_id;

        return session()->get("card_session_$hashid", $card->toArray());
    }

    private function createPreviewCanvas($cardWidth, $cardHeight)
    {
        $final = new Imagick();
        $final->newPseudoImage($cardWidth, $cardHeight, 'xc:transparent');

        return $final;
    }

    private function getCardPreviewUserPhotos(Card $card, array $cardSession)
    {
        if (!empty($cardSession['user_photo'])) {
            return $cardSession['user_photo'];
        }

        return $card->getImageElements()->toArray();
    }

    private function composePreviewUserPhotos(
        Imagick $final,
        array $userPhotos,
        array $clips,
        $cardFolder,
        $sessionId,
        $kumihanPath,
        $cardPreviewDpi,
        $cardWidth,
        $cardHeight,
    ) {
        $images = [];

        foreach ($userPhotos as $index => $image) {
            $clip = $clips[$image['style']['clipDbId']];

            $maskPath = $clip['style']['mask']['name'];
            $clipMask = $this->createPreviewCanvas($cardWidth, $cardHeight);
            $mask = new Imagick();
            $mask->readImageBlob(Storage::disk(config('common.storage_server.master.driver'))->get($maskPath));
            $mask->scaleImage($cardWidth, $cardHeight);
            $imagePath = $cardFolder . $sessionId . '/' . $image['image'];
            if (!Storage::disk(config('card.driver'))->exists($imagePath)) {
                $imagePath = config('kumihan.response.path') . $kumihanPath . $image['image'];
                $images[$index] = new Imagick(realpath($imagePath));
            } else {
                $images[$index] = new Imagick();
                $images[$index]->readImageBlob(Storage::disk(config('card.driver'))->get($imagePath));
            }

            $originalWidth = $images[$index]->getImageWidth();
            $images[$index]->scaleImage($originalWidth * $image['style']['scale'], 0);
            $images[$index]->rotateImage(new ImagickPixel('transparent'), $image['style']['angle']);

            $images[$index]->setImagePage(
                $images[$index]->getImageWidth(),
                $images[$index]->getImageHeight(),
                0,
                0,
            );

            $images[$index]->cropImage(
                $image['style']['crop']['width'] * $cardPreviewDpi / 25.4,
                $image['style']['crop']['height'] * $cardPreviewDpi / 25.4,
                $image['style']['crop']['x_coord'] * $cardPreviewDpi / 25.4,
                $image['style']['crop']['y_coord'] * $cardPreviewDpi / 25.4,
            );

            $clipMask->compositeImage(
                $images[$index],
                Imagick::COMPOSITE_OVER,
                $clip['style']['x_coordinate'] * $cardPreviewDpi / 25.4,
                $clip['style']['y_coordinate'] * $cardPreviewDpi / 25.4,
            );
            $clipMask->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);
            $final->compositeImage($clipMask, Imagick::COMPOSITE_OVER, 0, 0);
        }
    }

    private function getPreviewBackground(Card $card, $cardFolder, $sessionId, $cardWidth, $cardHeight)
    {
        $backgroundPath = $cardFolder . $sessionId . '/' . $card->image;
        if (!Storage::disk(config('card.driver'))->exists($backgroundPath)) {
            $backgroundPath = config('common.storage_server.master_path') . $card->image;
            $background = Image::read(Storage::disk('master_s3')->get($backgroundPath))->core()->native();
        } else {
            $background = Image::read(Storage::disk('data_s3')->get($backgroundPath))->core()->native();
        }

        $background->scaleImage($cardWidth, $cardHeight);

        return $background;
    }

    private function getPreviewCompleteImage(Card $card, $sessionId, $kumihanPath, $cardWidth, $cardHeight)
    {
        if ($card->has_atena) {
            return null;
        }

        $completePath = config('kumihan.response.path') . $sessionId . '/complete.png';
        if (!File::exists($completePath)) {
            $completePath = config('kumihan.response.path') . $kumihanPath . '/complete.png';
        }

        $complete = Image::read($completePath)->core()->native();
        $complete->scaleImage($cardWidth, $cardHeight);

        return $complete;
    }

    private function getSortedStampAndCropElements(array $cardSession)
    {
        $stampAndCropElements = array_merge($cardSession['stamp_photo'] ?? [], $cardSession['crop_photo'] ?? []);

        usort($stampAndCropElements, function ($a, $b) {
            return $a['style']['zIndex'] - $b['style']['zIndex'];
        });

        return $stampAndCropElements;
    }

    private function composeStampAndCropElements(Imagick $final, array $stampAndCropElements, $cardPreviewDpi)
    {
        $imageStamps = array_pluck($stampAndCropElements, 'style.imageId');
        $imagesIdList = $this->imageRepository->getListStampImage($imageStamps);

        foreach ($stampAndCropElements as $element) {
            if ($element['style']['type'] == config('card.element_type.crop')) {
                $imageName = $element['image'];
            } else {
                $imageId = $element['style']['imageId'];
                $imageName = str_replace('/thumb/', '/screen/', $imagesIdList[$imageId]);
            }
            $x = $element['style']['x_coordinate'] * $cardPreviewDpi / 25.4;
            $y = $element['style']['y_coordinate'] * $cardPreviewDpi / 25.4;
            $width = $element['width'] * $cardPreviewDpi / 25.4;
            $height = $element['height'] * $cardPreviewDpi / 25.4;
            $imgStampAndCrop = Image::read(public_path($imageName))->core()->native();
            $imgStampAndCrop->setImageFormat('png');
            $imgStampAndCrop->scaleImage($width, $height);
            $imgStampAndCrop->rotateImage(new ImagickPixel('transparent'), $element['style']['angle']);

            $final->compositeImage($imgStampAndCrop, Imagick::COMPOSITE_OVER, $x, $y);
        }
    }

    private function getAtenaPreviewCompleteImage($cardFolder, $sessionId, $kumihanPath, $cardWidth, $cardHeight)
    {
        $completePath = $cardFolder . $sessionId . '/complete.png';
        if (!File::exists($completePath)) {
            $completePath = config('kumihan.response.path') . $kumihanPath . '/complete.png';
        }

        $complete = Image::read($completePath)->core()->native();
        $complete->scaleImage($cardWidth, $cardHeight);

        return $complete;
    }

    private function storePreviewImage(Imagick $final, $jpgPath, $cardPreviewDpi, $backgroundBitDepth)
    {
        $final->setImageResolution($cardPreviewDpi, $cardPreviewDpi);
        $final->setImageUnits(Imagick::RESOLUTION_PIXELSPERINCH);
        $final->setImageDepth($backgroundBitDepth);
        $final->setCompression(imagick::COMPRESSION_LZW);
        $final->setImageFormat('jpg');
        Storage::disk(config('card.driver'))->put($jpgPath, $final->getImagesBlob());
    }

    private function updatePreviewImageState(Card $card, $previewName)
    {
        $card->image_preview = $previewName;
        $card->print_complete_text = config('card.image_name.complete_tif');
        $card->save();
    }

    // Get all cards to create pdf for printing
    public function findCardsToCreatePDF()
    {
        return $this->model->where([
            'is_complete_order' => $this->model::IS_COMPLETE_ORDER,
            'is_pdf_created' => $this->model::IS_NOT_PDF_CREATED,
            'file_rename_date' => Card::IS_NOT_RENAME_FILE,
            'file_rename_time' => Card::IS_NOT_RENAME_FILE,
        ])->get();
    }

    // Get all cards to send to api make print pdf
    public function findCardToMakePrintPDF()
    {
        return $this->model->where([
            'is_pdf_created' => $this->model::IS_NOT_PDF_CREATED,
        ])->get();
    }

    // Update data creating pdf
    public function updateCardCreatingPdf($cardIds)
    {
        DB::table('cards')->whereIn('id', $cardIds)->update([
            'is_pdf_created' => Card::IS_PDF_CREATING,
        ]);

        Log::info('CardIds creating PDF: ' . json_encode($cardIds));
    }

    // Update data not created pdf
    public function updateCardNotCreatedPdf($cardId)
    {
        DB::table('cards')->where('id', $cardId)->update([
            'is_pdf_created' => Card::IS_NOT_PDF_CREATED,
            'file_rename_date' => Card::IS_NOT_RENAME_FILE,
            'file_rename_time' => Card::IS_NOT_RENAME_FILE,
        ]);
    }

    // Update data after push pdf to server
    public function updateCardCompleted($cardId)
    {
        $cardInfo = [];
        $cardInfo['is_pdf_created'] = Card::IS_PDF_CREATED;
        $cardInfo['file_rename_date'] = date('Y-m-d');
        $cardInfo['file_rename_time'] = date('H:i:s');
        DB::table('cards')->where('id', $cardId)->update($cardInfo);

        Log::info('card_id: ' . $cardId);
        Log::info('card_info: ' . json_encode($cardInfo));
    }

    public function updateCompleteOrder($kumihanIds)
    {
        return DB::table('cards')->whereIn('kumihan_id', $kumihanIds)->update([
            'is_complete_order' => Card::IS_COMPLETE_ORDER,
        ]);
    }

    public function fetchPrintImage($card, $localPath)
    {
        $design = $this->designRepository->findDesignByMaterialIdAndDesignId(
            $card->material_id,
            $card->product_code,
            ['design_image_url'],
        );
        $designImageUrl = config('filesystems.disks.s3.url')
            . "/products/{$design->design_image_url}";
        if (!$contentFile = file_get_contents($designImageUrl)) {
            return false;
        }

        return file_put_contents($localPath, $contentFile);
    }

    // Create Print Image for only design have photo_flg = 1
    public function exportPDF($card, $retryTimes = 0)
    {
        try {
            if ($card->isFromSejApp()) {
                $retryTimes++;
                $backgroundPath = $card->print_background;

                if (!Storage::disk(config('common.storage_server.master.driver'))->exists($backgroundPath)) {
                    Log::error('The background image does not exist, please check it. Path :' . $backgroundPath . 'KI: ' . $card->kumihan_id);

                    return false;
                }
                $imageName = sprintf(
                    config('common.storage_server.data.print_image.name_pattern'),
                    $card->kumihan_id,
                );
                $imagePath = sprintf(
                    config('common.storage_server.data.print_image.path'),
                    substr($card->kumihan_id, -3),
                );
                if (!File::isDirectory($imagePath)) {
                    File::makeDirectory($imagePath, 0777, true, true);
                }
                $printImageName = $imagePath . $imageName;
                //Copy file from s3 at print_image from card to printImageName
                File::put($printImageName, Storage::disk(config('common.storage_server.master.driver'))->get($backgroundPath));
                if (!File::exists($printImageName)) {
                    Log::error('Error: Export to Sej app card is NG, card: ' . $card->kumihan_id);

                    return false;
                }

                Log::info('Save Sej app card card to Storage server is OK, path: ' . $printImageName);
                return true;
            }
            //If design has not user upload photo, return true
            if (!has_photo_frame($card)) {
                return true;
            }

            $retryTimes++;
            $isSuccess = false;
            $cardPrintDpi = config('card.print_dpi');
            $cardPreviewDpi = config('card.preview_dpi');

            $backgroundPath = $card->print_background;

            if (!Storage::disk(config('common.storage_server.master.driver'))->exists($backgroundPath)) {
                Log::error('The background image does not exist, please check it. Path :' . $backgroundPath . 'KI: ' . $card->kumihan_id);

                return false;
            }

            $rgbProfileContent = file_get_contents(storage_path('app') . '/sRGB_v4_ICC_preference.icc');
            $cmykProfileContent = file_get_contents(storage_path('app') . '/JapanColor2011Coated.icc');
            $background = new Imagick();
            $background->readImageBlob(Storage::disk(config('common.storage_server.master.driver'))->get($backgroundPath));
            $backgroundBitDepth = $background->getImageDepth();
            $backgroundWidth = $background->getImageWidth();
            $backgroundHeight = $background->getImageHeight();
            $dpi = $this->getDpi($backgroundWidth, $card->style['width']);

            $backgroundColorspace = $background->getImageColorspace();
            if ($backgroundColorspace != Imagick::COLORSPACE_CMYK) {
                $backgroundProfile = $background->getImageProfiles('*', false);
                $hasIccProfile = (array_search('icc', $backgroundProfile) !== false);

                if ($hasIccProfile === false) {
                    $background->profileImage('icc', $rgbProfileContent);
                }
            }

            if ($card->isCmyk()) {
                $background->profileImage('icc', $cmykProfileContent);
                $background->transformImageColorspace(Imagick::COLORSPACE_CMYK);
            }

            $isExport350Dpi = !has_photo_frame($card) && !is_black_white_design($card);

            if ($isExport350Dpi && $dpi == $cardPreviewDpi) {
                $cardPrintDpi = config('card.preview_dpi');
            }

            $ratioToScale = $cardPrintDpi / $cardPreviewDpi;

            // Get complete image (tiff)
            // if (isset($card->is_complete_image_created) && !$card->is_complete_image_created) {
            //     $result = $this->kumihanService->getCompleteText($card, $dpi);

            //     if ($result['status'] == 'error') {
            //         Log::error($result['message']);

            //         return false;
            //     }
            // }

            $cardWidth = $card->style['width'] * $cardPrintDpi / 25.4;
            $cardHeight = $card->style['height'] * $cardPrintDpi / 25.4;
            $final = new Imagick();
            $final->newPseudoImage($backgroundWidth, $backgroundHeight, 'xc:transparent');
            if ($card->isCmyk()) {
                $final->setImageColorspace(Imagick::COLORSPACE_CMYK);
            } else {
                $final->setImageColorspace(Imagick::COLORSPACE_SRGB);
            }

            $clips = $card->getClipElements()->keyBy('id')->toArray();
            // Generate photo of user
            $images = [];
            if (count($clips) != count($card->getImageElements())) {
                Log::error("Retry $retryTimes (Failed): User uploaded photo is not equal to mask number. KumihanID: $card->kumihan_id");
                $this->copyCardUserPhotoToMountedStorage($card);
                return false;
            }

            foreach ($card->getImageElements() as $index => $image) {
                $clip = $clips[$image['style']['clipDbId']];
                $imagePath = $image['image'];
                $maskPath = $clip['style']['mask']['name'];
                if (!Storage::disk(config('common.storage_server.master.driver'))->exists($maskPath)) {
                    Log::error('Retry ' . $retryTimes . ' (Failed):The mask image does not exist, please check it. Path :' . $maskPath . ' KumihanID: ' . $card->kumihan_id . ')');

                    return false;
                }

                if (!Storage::disk(config('common.storage_server.data.driver'))->exists($imagePath)) {
                    Log::error('Retry ' . $retryTimes . ' (Failed):The user upload image does not exist, please check it. Path :' . $imagePath . ' KumihanID: ' . $card->kumihan_id . ')');

                    $this->copyCardUserPhotoToMountedStorage($card);
                    return false;
                }

                $clipMask = new Imagick();
                $clipMask->newPseudoImage($backgroundWidth, $backgroundHeight, 'xc:transparent');
                $mask = new Imagick();
                $mask->readImageBlob(Storage::disk(config('common.storage_server.master.driver'))->get($maskPath));
                $images[$index] = new Imagick();
                $images[$index]->readImageBlob(Storage::disk(config('common.storage_server.data.driver'))->get($imagePath));
                $originalWidth = $images[$index]->getImageWidth();
                $images[$index]->scaleImage($originalWidth * $image['style']['scale'], 0);
                $images[$index]->rotateImage(new ImagickPixel('transparent'), $image['style']['angle']);
                $images[$index]->setImagePage(
                    $images[$index]->getImageWidth(),
                    $images[$index]->getImageheight(),
                    0,
                    0,
                );

                $images[$index]->cropImage(
                    $image['style']['crop']['width'] * $cardPreviewDpi / 25.4,
                    $image['style']['crop']['height'] * $cardPreviewDpi / 25.4,
                    $image['style']['crop']['x_coord'] * $cardPreviewDpi / 25.4,
                    $image['style']['crop']['y_coord'] * $cardPreviewDpi / 25.4,
                );

                $images[$index]->scaleImage($images[$index]->getImageWidth() * $ratioToScale, 0);
                $clipMask->compositeImage(
                    $images[$index],
                    Imagick::COMPOSITE_OVER,
                    $clip['style']['x_coordinate'] * $cardPrintDpi / 25.4,
                    $clip['style']['y_coordinate'] * $cardPrintDpi / 25.4,
                );
                $clipMask->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0); // do not delete

                $clipMaskProfile = $clipMask->getImageProfiles('*', false);
                $hasIccProfile = (array_search('icc', $clipMaskProfile) !== false);
                if ($hasIccProfile === false && !$card->isCmyk()) {
                    $clipMask->profileImage('icc', $rgbProfileContent);
                }
                if ($card->isCmyk()) {
                    $clipMask->profileImage('icc', $cmykProfileContent);
                }

                $final->compositeImage(
                    $clipMask,
                    Imagick::COMPOSITE_OVER,
                    0,
                    0,
                );
            }

            $final->compositeImage($background, Imagick::COMPOSITE_OVER, 0, 0);

            // Add stamp and crop images
            $listType = [
                config('card.element_type.stamp'),
                config('card.element_type.crop'),
            ];
            $stampAndCropElements = $card->getMutiTypeElements($listType);
            $imageStamps = $stampAndCropElements->pluck('style.imageId')->toArray();
            $imagesIdList = ImgModel::whereIn('id', $imageStamps)->pluck('image', 'id')->toArray();
            foreach ($stampAndCropElements as $element) {
                if ($element->style['type'] == config('card.element_type.crop')) {
                    $imageName = $element->image;
                } else {
                    $imageId = $element->style['imageId'];
                    $imageName = str_replace('/thumb/', '/print/', $imagesIdList[$imageId]);
                }
                $x = $element['style']['x_coordinate'] * $cardPrintDpi / 25.4;
                $y = $element['style']['y_coordinate'] * $cardPrintDpi / 25.4;
                $width = $element->width * $cardPrintDpi / 25.4;
                $height = $element->height * $cardPrintDpi / 25.4;
                $imgStampAndCrop = Image::read(public_path($imageName))->core()->native();
                $imgStampAndCrop->setImageFormat('png');
                $imgStampAndCrop->scaleImage($width, $height);
                $imgStampAndCrop->rotateImage(new ImagickPixel('transparent'), $element['style']['angle']);

                $imageProfile = $imgStampAndCrop->getImageProfiles('*', false);
                $hasIccProfile = (array_search('icc', $imageProfile) !== false);
                if ($hasIccProfile === false) {
                    $imgStampAndCrop->profileImage('icc', $rgbProfileContent);
                }
                if ($card->isCmyk()) {
                    $imgStampAndCrop->profileImage('icc', $cmykProfileContent);
                }

                $final->compositeImage($imgStampAndCrop, Imagick::COMPOSITE_OVER, $x, $y);
            }

            // generate temp card
            if (has_photo_frame($card) || $card->is_free_design) {
                $imageName = sprintf(
                    config('common.storage_server.data.print_image.name_pattern'),
                    $card->kumihan_id,
                );
                $imagePath = sprintf(
                    config('common.storage_server.data.print_image.path'),
                    substr($card->kumihan_id, -3),
                );
                if (!File::isDirectory($imagePath)) {
                    File::makeDirectory($imagePath, 0777, true, true);
                }
                $printImageName = $imagePath . $imageName;
                $tempCard = clone $final;
                $tempCard->setImageResolution($cardPrintDpi, $cardPrintDpi);
                $tempCard->setImageUnits(Imagick::RESOLUTION_PIXELSPERINCH);
                // if ($card->style['photo_finishing_flg'] != Card::IS_RGB_FLG) {
                //     $tempCardPath = $tempCardFolder . $card->kumihan_id . '_CMYK.jpg';
                //     $tempCard->transformImageColorspace(Imagick::COLORSPACE_CMYK);
                // }
                $tempCard->setImageDepth($backgroundBitDepth);
                $tempCard->setCompression(Imagick::COMPRESSION_LZW);
                $tempCard->writeImage($printImageName);
                $tempCard->destroy();
                if (!File::exists($printImageName)) {
                    Log::error('Error: Export to temp card is NG, card: ' . $card->kumihan_id);

                    return false;
                }

                Log::info('Save temp card to Storage server is OK, path: ' . $printImageName);
            }

            return true;
        } catch (Throwable $th) {
            Log::error('Error: Export to PDF is NG, card: ' . $card->kumihan_id);
            Log::error($th);

            return false;
        }
    }

    public function getDpi($pixel, $milimet)
    {
        return (int) round(($pixel * $this->MM_TO_PX) / $milimet);
    }

    public function copyCardFilesToMountedStorage($card)
    {
        return $this->copyCardUserPhotoToMountedStorage($card);
    }

    protected function copyCardUserPhotoToMountedStorage($card)
    {
        $cardFolder = self::getCardFolder($card);
        $hasError = false;

        foreach ($card->getImageElements() as $element) {
            try {
                $sourceFilePath = $cardFolder . $element->image;
                $sourceFileExt = File::extension($sourceFilePath);

                $destinationFilePath = sprintf(
                    config('common.storage_server.data.user_photo.name_pattern'),
                    substr($card->kumihan_id, -3),
                    $card->kumihan_id,
                    sprintf('%02s', $element->style['mask_id']),
                    $sourceFileExt,
                );
                if (!Str::startsWith($element->image, '/mnt/')) {
                    if (!Storage::disk(config('card.driver'))->exists($sourceFilePath)) {
                        Log::info('User upload file not exists : ' . $sourceFilePath . ' KI: ' . $card->kumihan_id);
                        $hasError = true;
                        foreach (Storage::disk(config('card.driver'))->directories($cardFolder) as $directory) {
                            if (Storage::disk(config('card.driver'))->exists($directory . "/$element->image")) {
                                Log::info('User upload found at  : ' . $directory . "/$element->image" . ' KI: ' . $card->kumihan_id);
                                Storage::disk(config('card.driver'))->copy($directory . "/$element->image", $cardFolder . $element->image);
                            }
                        }
                    } else {
                        Log::debug('Copy user upload photo to mounted storage. KI: ' . $card->kumihan_id, [
                            'sourceFilePath' => $sourceFilePath,
                            'destinationFilePath' => $destinationFilePath,
                        ]);
                        $uploadFileContent = Storage::disk(config('card.driver'))->get($sourceFilePath);
                        Storage::disk(config('common.storage_server.data.driver'))->put($destinationFilePath, $uploadFileContent);
                        // Storage::disk(config('card.driver'))->delete($sourceFilePath);
                        Log::info('Push file to Storage server is OK. Destination file: ' . $destinationFilePath);
                        $element->image = $destinationFilePath;
                        $element->save();
                    }
                }
            } catch (Throwable $th) {
                Log::error($th);
                $hasError = true;

                continue;
            }
        }

        if (!$hasError) {
            foreach (Storage::disk(config('card.driver'))->directories($cardFolder) as $directory) {
                // Storage::disk(config('card.driver'))->delete(Storage::disk(config('card.driver'))->files($directory));
                Log::info('Remove folder user upload temp: ' . $directory);
            }

            if (Storage::disk(config('card.driver'))->exists($cardFolder . '/' . config('card.image_name.edit_design') . '.jpg')) {
                Storage::disk(config('card.driver'))->delete($cardFolder . '/' . config('card.image_name.edit_design') . '.jpg');
                Log::info('Remove edit design file: ' . $cardFolder . '/' . config('card.image_name.edit_design') . '.jpg');
            }
        }

        return true;
    }

    protected function copyCardThumbnailToMountedStorage($card)
    {
        try {
            if ($card->has_atena) {
                $sourceFilePath = self::getCardFolder($card)
                    . config('card.image_name.atena_preview');
            } else {
                $sourceFilePath = self::getCardFolder($card)
                    . $card->image_preview;
            }
            if (!File::exists($sourceFilePath) || !$card->image_preview) {
                Log::error('Copy thumbnail image to mounted storage, SourcePath does not exist.', [
                    'kumihan_id' => $card->kumihan_id,
                    'imagePreview' => $card->image_preview ?? null,
                    'sourceFilePath' => $sourceFilePath,
                ]);

                throw new Exception($sourceFilePath . ' does not exist.');
            }


            $sourceFileExt = File::extension($sourceFilePath);

            $destinationFilePath = sprintf(
                config('common.card_thumbnail.mounted_storage'),
                substr($card->kumihan_id, -3),
                $card->kumihan_id,
                $sourceFileExt,
            );

            Log::debug('Copy thumbnail image to mounted storage. KI: ' . $card->kumihan_id, [
                'sourceFilePath' => $sourceFilePath,
                'destinationFilePath' => $destinationFilePath,
            ]);

            if (!is_dir(pathinfo($destinationFilePath, PATHINFO_DIRNAME))) {
                File::makeDirectory(pathinfo($destinationFilePath, PATHINFO_DIRNAME), 0775, true);
            }

            if (File::copy($sourceFilePath, $destinationFilePath)) {
                Log::info('Push file to Storage server is OK. Destination file: ' . $destinationFilePath);
            }

            return true;
        } catch (Throwable $th) {
            Log::error($th);

            return false;
        }
    }

    public function findCardById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function findByAttributes(array $attributes)
    {
        return $this->model->where($attributes)->first();
    }

    public function updateKumihanCountCard(Card $card)
    {
        $kumihanCount = $card->style['kumihan_count'];
        $card['style->kumihan_count'] = $kumihanCount + 1;
        $card->save();
    }

    public function updateAddressPrintNum(Card $card)
    {
        // Update address_print_num
        $card->address_print_num = $card->receivers()->wherePivot('image_path', '!=', null)->count();
        // Update atena_kumihan_count_complete
        $card->atena_kumihan_count_complete = $card->style['kumihan_count'];
        // Save card
        $card->save();
        Log::info('Atena print num', [
            'kumihan_id' => $card->kumihan_id,
            'address_print_num' => $card->address_print_num,
            'kumihan_count' => $card->style['kumihan_count'],
        ]);
    }

    public function saveSessionIntoDB($hashid)
    {
        try {
            $cardSession = session()->get("card_session_$hashid");
            $card = $this->findCardByHashId($hashid);

            DB::beginTransaction();
            if ($cardSession) {
                $cardSession['style']['edit_count'] = $card->style['edit_count'];
                unset($cardSession['style']['kumihan_count']);
                $card = $this->update($cardSession, $card);
            }

            $card->elements()
                ->where(function ($query) {
                    return $query->where('style->type', config('card.element_type.background'))
                        ->orWhere('style->type', config('card.element_type.image'))
                        ->orWhere('style->type', config('card.element_type.text'))
                        ->orWhere('style->type', config('card.element_type.group'))
                        ->orWhere('style->type', config('card.element_type.stamp'))
                        ->orWhere('style->type', config('card.element_type.crop'));
                })
                ->delete();

            $card->elements()->createMany($cardSession['elements'] ?? []);
            $card->elements()->createMany($cardSession['user_photo'] ?? []);
            $sessionId = get_session_id();
            // copy user photo from temp folder to card folder
            $tempFolder = self::getCardFolder($card) . $sessionId;
            $cardFolder = self::getCardFolder($card);
            foreach (Storage::disk(config('card.driver'))->files($tempFolder) as $file) {
                $toFilePath = $cardFolder . basename($file);
                Storage::disk(config('card.driver'))->copy($file, $toFilePath);
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e);

            return back()->withErrors([
                'message' => __('message.common_error'),
            ]);
        }
    }

    public function isSessionTimeout($hashid)
    {
        return session()->has("card_session_$hashid");
    }

    public function duplicateCard($kumihanId)
    {
        $card = $this->findCardByKumihanId($kumihanId);

        $newCard = $card->replicate();
        $newCard->save();
        $newCard->update([
            'kumihan_id' => generateKumihanId($newCard),
        ]);

        $copyKumihan = app(KumihanService::class)->duplicateKumihanForMydesign($card->kumihan_id, $newCard->kumihan_id);
        if ($copyKumihan != 'OK') {
            throw new Exception(
                Lang::has("kumihan.error_message.{$copyKumihan}")
                    ? __("kumihan.error_message.{$copyKumihan}")
                    : __('message.common_error'),
                Response::HTTP_BAD_REQUEST,
            );
        }

        if ($newCard->atena_kumihan_id && $newCard->style['kumihan_count'] > 0) {
            $newCard->update([
                'atena_kumihan_id' => generateKumihanId($newCard),
            ]);
            $copyKumihan = app(KumihanService::class)->duplicateKumihanForMydesign(
                $card->atena_kumihan_id,
                $newCard->atena_kumihan_id,
                1,
            );
            if ($copyKumihan != 'OK') {
                throw new Exception(
                    Lang::has("kumihan.error_message.{$copyKumihan}")
                        ? __("kumihan.error_message.{$copyKumihan}")
                        : __('message.common_error'),
                    Response::HTTP_BAD_REQUEST,
                );
            }
        }

        $card->replicateRelations($newCard);
        $card->replicateFolder($newCard);

        return $newCard;
    }

    public function moveCardImage($cardFolder, $materialId, $designId, $defaultScreenName)
    {
        try {
            $imageName = $this->designService->getDesignImage($materialId, $designId);

            if (env('AWS_BUCKET')) {
                $url = env('AWS_URL') . '/' . env('AWS_BUCKET') . "/products/$imageName";
            } else {
                $url = env('AWS_URL') . "/products/$imageName";
            }

            file_put_contents("{$cardFolder}{$defaultScreenName}", file_get_contents($url));
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @param $inputs
     * @param $hashId
     *
     * @return array
     * @throws Exception
     */
    public function checkSupportAtena($inputs, $hashId)
    {
        $designId = $inputs['design_id'];
        $materialId = $inputs['material_id'];
        $agencyId = $inputs['agency_id'];
        $siteId = $inputs['site_id'];

        $areaAgency = $this->areaAgencyRepository->getAreaAgencyByAreaIdAndAgencyId($siteId, $agencyId);

        if (!$areaAgency) {
            Log::error('Area agency was not found. ' . json_encode($inputs));

            throw new Exception('Area agency was not found.');
        }

        if (empty($areaAgency->atena_support) || !in_array($areaAgency->atena_support, AreaAgency::ATENA_SUPPORT)) {
            return [
                'status' => true,
                'redirect_url' => route('cards.redirect-to-cart', ['hashId' => $hashId]),
                'has_atena' => false,
            ];
        }
        if ($areaAgency->atena_end_date && Carbon::parse($areaAgency->atena_end_date)->endOfMinute()->lt(Carbon::now()->format('Y-m-d H:i:s'))) {
            return [
                'status' => true,
                'redirect_url' => route('cards.redirect-to-cart', ['hashId' => $hashId]),
                'has_atena' => false,
            ];
        }
        $design = $this->designRepository->findDesignByMaterialIdAndDesignId(
            $materialId,
            $designId,
            ['atena_support'],
        );

        if (!$design) {
            Log::error('Design was not found. ' . json_encode($inputs));

            throw new Exception('Design was not found.');
        }

        if ($design->atena_support != Design::ATENA_SUPPORT) {
            return [
                'status' => true,
                'redirect_url' => route('cards.redirect-to-cart', ['hashId' => $hashId]),
                'has_atena' => false,
            ];
        }

        return [
            'status' => true,
            'redirect_url' => '',
            'has_atena' => true,
        ];
    }

    /**
     * @param $hashId
     * @return string
     */
    public function redirectToCart($hashId)
    {
        $card = $this->findCardByHashId($hashId);
        $designId = $card->design_id;
        $materialId = $card->material_id;
        $agencyId = $card->agency_id;
        $siteId = $card->area_id;

        if (!$agencyId || !$siteId) {
            Log::error('Session common card is empty.');

            abort(404);
        }

        $agency = $this->agencyRepository->findAgencyById($agencyId, 'agency_ename');

        if (!$agency) {
            Log::error("Agency {$agencyId} not found. ");

            abort(404);
        }

        $agencyEName = $agency->agency_ename;

        $uri = sprintf(config('ecsite.redirect_update_card_info'), $agencyEName, $siteId);

        return trim(env('ECSITE_URL'), '/') . $uri;
    }

    public function getSessionExchange($hashId)
    {
        $exchange = [
            'kousei_email' => '',
            'oemail' => '',
            'kousei_flg' => 0,
            'accept_preview' => 1,
            'no_deal_reason' => '',
        ];
        if (session()->has("exchange_$hashId")) {
            $exchange = session()->get("exchange_$hashId");
        }

        return $exchange;
    }

    public function reOrderCard($card)
    {
        $newCard = $card->replicate();
        $newCard->save();

        $newCard->kumihan_id = generateKumihanId($newCard);
        if ($card->atena_kumihan_id) {
            $newCard->atena_kumihan_id = generateKumihanId($newCard);
        }
        $newCard->save();

        $card->replicateRelations($newCard);
        $card->replicateFolder($newCard);

        return $newCard;
    }

    public function updateAuthToCard($card, $userId)
    {
        $card->update([
            'user_id' => $userId,
            'user_login_type' => config('ecsite.login_types.is_logged_in'),
        ]);
    }

    public function cardCart($card, $exchange)
    {
        $addresses = $card->addresses ?? [];
        $participants = $card->participants ?? [];
        $company = $card->company;
        $checkCardSupportAtena = $this->checkSupportAtena([
            'design_id' => $card->product_code,
            'material_id' => $card->material_id,
            'agency_id' => $card->agency_id,
            'site_id' => $card->area_id,
        ], $card->hash_id);
        $checkSupportAtena = $checkCardSupportAtena['has_atena'];

        $userId = !empty(auth_user($card->agency_id)) ? auth_user($card->agency_id)->id : null;
        $virtualUser = app(User::class)->find($card->user_id);

        if ($card->address_print_num > 0) {
            $atenaDM = [
                'aid' => $card->aid,
                'bcsid' => $card->bcsid,
            ];
        }

        $mourmingCard = $card->mourningCard ?? [];
        $kumihanError = $this->getKumihanError($exchange, $card->hash_id);
        $otherInfo = !empty($card->dm_info) ? $card->dm_info['other_info'] : [];
        Log::info([
            'card_id' => $card->id,
            'kumihan_id' => $card->kumihan_id,
            'kumihan_err_kbn' => $kumihanError,
        ]);

        session([
            "cart_{$card->kumihan_id}" => [
                'item_id' => $card->kumihan_id,
                'option1_flg' => null,
                'option2_flg' => null,
                'option3_flg' => null,
                'option4_flg' => null,
                'option5_flg' => null,
                'hin_kbn' => 0,
                'fukusu_sw' => null,
                'tuika_sw' => null,
                'tok_cd' => $card->agency_id,
                'OORDERID' => null,
                'special_field' => null,
                'oname' => null,
                'mei_1_soroe' => $card->dm_info['mei_1_soroe'] ?? null,
                'atena_flg' => $card->address_print_num > 0 ? 1 : 0,
                'atena_font' => null,
                'atena_aid' => $atenaDM['aid'] ?? null,
                'atena_oid' => $atenaDM['bcsid'] ?? null,
                'atena_pid' => null,
                'otelno' => $this->getOtelno($otherInfo['otelno'] ?? null),
                'ofaxno' => null,
                'oemail' => $exchange['oemail'] ?? null,
                'dname' => $otherInfo['dname'] ?? null,
                'daddress_new' => null,
                'daddress' => isset($otherInfo['daddress']) ? explode(' ', $otherInfo['daddress']) : null,
                'dtelno' => $this->getDtelno($otherInfo['dtelno'] ?? null),
                'eiri_no' => null,
                'stamp_type' => null,
                'typeno' => $card->product_code,
                'plate_size' => null,
                'ukibori' => null,
                'option_text' => null,
                'option_address' => null,
                'maisu' => !empty($otherInfo) && $otherInfo['maisu'] ? $otherInfo['maisu'] : 0,
                'layout_no' => null,
                'bunsyo_no' => (array_key_exists('textno_new', $card->style) && $card->style['textno_new'] == '90') ? $card->style['textno_new'] : ($card->style['text_no'] == '99' ? null : $card->style['text_no']),
                'zip_waku_flg' => null,
                'hag_syurui' => 2,
                'sei_wa' => is_mochuu($card->style['item_kbn'])
                    ? ($mourmingCard->calendar ?? null)
                    : ($card->newYearCard->calendar ?? null),
                'topping_a' => null,
                'topping_b' => null,
                'font_no_j' => null,
                'font_no_e' => null,
                'font_no' => $card->style['shotai'],
                'color_no_logo' => null,
                'color_no' => null,
                'color_no_line' => null,
                'color_no_etc' => null,
                'color_no_topping_a' => null,
                'color_no_topping_b' => null,
                'dmno' => $card->dm_info['dm_number'] ?? null,
                'gazou_info' => null,
                'bikou' => null,
                'layout_no_r' => null,
                'font_no_seimei' => null,
                'font_no_company' => null,

                'addresstitle1' => ($addresses[0]['address_type'] ?? null) == 'others'
                    ? ($addresses[0]['type'] ?? null)
                    : (config('common.addresses.address_type')[$addresses[0]['address_type'] ?? null] ?? null),
                'zipmark1' => null,
                'zip1_1' => get_postcode_array($addresses[0]['postcode'] ?? null)[0] ?? null,
                'zip1_2' => get_postcode_array($addresses[0]['postcode'] ?? null)[1] ?? null,
                'address1_1' => ($addresses[0]['prefecture'] ?? null) . ($addresses[0]['city'] ?? null),
                'address1_2' => $addresses[0]['street'] ?? null,
                'address1_3' => $addresses[0]['building_name'] ?? null,
                'telmark1_1' => $addresses[0]['phone_fax'][0]['key'] ?? null,
                'telno1_1' => ($addresses[0]['phone_fax'][0]['phone_1'] ?? null)
                        . (($addresses[0]['phone_fax'][0]['phone_2'] ?? null) ? '(' . ($addresses[0]['phone_fax'][0]['phone_2']) . ')' : null)
                        . ($addresses[0]['phone_fax'][0]['phone_3'] ?? null),
                'teltitle1_1' => null,
                'telmark1_2' => $addresses[0]['phone_fax'][1]['key'] ?? null,
                'telno1_2' => ($addresses[0]['phone_fax'][1]['phone_1'] ?? null)
                        . (($addresses[0]['phone_fax'][1]['phone_2'] ?? null) ? '(' . ($addresses[0]['phone_fax'][1]['phone_2']) . ')' : null)
                        . ($addresses[0]['phone_fax'][1]['phone_3'] ?? null),
                'teltitle1_2' => null,
                'telmark1_3' => $addresses[0]['phone_fax'][2]['key'] ?? null,
                'telno1_3' => ($addresses[0]['phone_fax'][2]['phone_1'] ?? null)
                        . (($addresses[0]['phone_fax'][2]['phone_2'] ?? null) ? '(' . ($addresses[0]['phone_fax'][2]['phone_2']) . ')' : null)
                        . ($addresses[0]['phone_fax'][2]['phone_3'] ?? null),
                'teltitle1_3' => null,

                'addresstitle2' => ($addresses[1]['address_type'] ?? null) == 'others'
                    ? ($addresses[1]['type'] ?? null)
                    : (config('common.addresses.address_type')[$addresses[1]['address_type'] ?? null] ?? null),

                'zipmark2' => null,
                'zip2_1' => get_postcode_array($addresses[1]['postcode'] ?? null)[0] ?? null,
                'zip2_2' => get_postcode_array($addresses[1]['postcode'] ?? null)[1] ?? null,
                'address2_1' => ($addresses[1]['prefecture'] ?? null) . ($addresses[1]['city'] ?? null),
                'address2_2' => $addresses[1]['street'] ?? null,
                'address2_3' => $addresses[1]['building_name'] ?? null,
                'telmark2_1' => $addresses[1]['phone_fax'][0]['key'] ?? null,
                'telno2_1' => ($addresses[1]['phone_fax'][0]['phone_1'] ?? null)
                        . (($addresses[1]['phone_fax'][0]['phone_2'] ?? null) ? '(' . ($addresses[1]['phone_fax'][0]['phone_2']) . ')' : null)
                        . ($addresses[1]['phone_fax'][0]['phone_3'] ?? null),
                'teltitle2_1' => null,
                'telmark2_2' => $addresses[1]['phone_fax'][1]['key'] ?? null,
                'telno2_2' => ($addresses[1]['phone_fax'][1]['phone_1'] ?? null)
                        . (($addresses[1]['phone_fax'][1]['phone_2'] ?? null) ? '(' . ($addresses[1]['phone_fax'][1]['phone_2']) . ')' : null)
                        . ($addresses[1]['phone_fax'][1]['phone_3'] ?? null),

                'teltitle2_2' => null,

                'email1' => $card->email_url[0]['value'] ?? null,
                'email2' => $card->email_url[1]['value'] ?? null,

                'company_name' => $company->name ?? null,
                'company_jigyo' => $company->department ?? null,

                'busyo1' => null,
                'busyo2' => null,
                'busyo3' => null,
                'busyo4' => null,
                'busyo5' => null,
                'busyo6' => null,
                'company_item' => null,

                'name1_sei' => $card->last_name ?? null,
                'name1_mei' => $card->first_name ?? null,
                'name1_paren' => $card->furigana_old_last_name_age ?? null,
                'name1_katagaki' => $card->furigana_title ?? null,
                'name1_sei_ruby' => $card->last_name_furigana ? implode('；', $card->last_name_furigana) : null,
                'name1_ruby' => $card->first_name_furigana ? implode('；', $card->first_name_furigana) : null,
                'name1_katagaki1' => null,
                'name1_katagaki2' => null,
                'name1_sei_paren' => null,
                'name1_seimei_e' => null,
                'name1_katagaki1_r' => null,
                'name1_katagaki2_r' => null,
                'name1_sei_r' => null,
                'name1_mei_r' => null,
                'name1_seimei_e_r' => null,

                'etc1' => null,
                'etc2' => null,
                'etc3' => null,
                'etc4' => null,
                'etc5' => null,
                'address1_r' => null,
                'telmark1_1_r' => null,
                'telno1_1_r' => null,
                'teltitle1_1_r' => null,
                'telmark1_2_r' => null,
                'telno1_2_r' => null,
                'teltitle1_2_r' => null,
                'telmark1_3_r' => null,
                'telno1_3_r' => null,
                'teltitle1_3_r' => null,
                'address2_r' => null,
                'telmark2_1_r' => null,
                'telno2_1_r' => null,
                'teltitle2_1_r' => null,
                'telmark2_2_r' => null,
                'telno2_2_r' => null,
                'teltitle2_2_r' => null,
                'email1_r' => null,
                'email2_r' => null,
                'company_name_r' => null,
                'busyo1_r' => null,
                'busyo2_r' => null,
                'busyo3_r' => null,
                'busyo4_r' => null,
                'busyo5_r' => null,
                'busyo6_r' => null,
                'company_item_r' => null,
                'etc1_r' => null,
                'etc2_r' => null,
                'etc3_r' => null,
                'etc4_r' => null,
                'etc5_r' => null,
                'addresstitle1_r' => null,
                'addresstitle2_r' => null,
                'font_no_seimei_r' => null,
                'font_no_company_r' => null,
                'eigyo_item1_r' => null,
                'eigyo_item2_r' => null,
                'eigyo_item3_r' => null,
                'eigyo_item4_r' => null,
                'eigyo_item5_r' => null,
                'bank_kouza_type' => null,
                'bank_furisaki' => null,
                'bank_kouza_no' => null,
                'bank_kouza_name' => null,
                'data_upper' => null,
                'data_lower' => null,
                'raku_font' => null,
                'raku_layout' => null,

                'tk_name1_sei' => $participants[0]['last_name'] ?? null,
                'tk_name1_mei' => $participants[0]['first_name'] ?? null,
                'tk_name1_mei_paren' => $participants[0]['furigana_old_last_name_age'] ?? null,
                'tk_name1_katagaki' => $participants[0]['furigana_title'] ?? null,
                'tk_name1_sei_ruby' => implode('；', $participants[0]['last_name_furigana'] ?? []) ?? null,
                'tk_name1_mei_ruby' => implode('；', $participants[0]['first_name_furigana'] ?? []) ?? null,
                'tk_name1_sei_paren' => null,
                'tk_name1_ruby' => null,

                'tk_name2_sei' => $participants[1]['last_name'] ?? null,
                'tk_name2_mei' => $participants[1]['first_name'] ?? null,
                'tk_name2_mei_paren' => $participants[1]['furigana_old_last_name_age'] ?? null,
                'tk_name2_katagaki' => $participants[1]['furigana_title'] ?? null,
                'tk_name2_sei_ruby' => implode('；', $participants[1]['last_name_furigana'] ?? []) ?? null,
                'tk_name2_mei_ruby' => implode('；', $participants[1]['first_name_furigana'] ?? []) ?? null,
                'tk_name2_sei_paren' => null,

                'tk_name3_sei' => $participants[2]['last_name'] ?? null,
                'tk_name3_mei' => $participants[2]['first_name'] ?? null,
                'tk_name3_mei_paren' => $participants[2]['furigana_old_last_name_age'] ?? null,
                'tk_name3_katagaki' => $participants[2]['furigana_title'] ?? null,
                'tk_name3_sei_ruby' => implode('；', $participants[2]['last_name_furigana'] ?? []) ?? null,
                'tk_name3_mei_ruby' => implode('；', $participants[2]['first_name_furigana'] ?? []) ?? null,
                'tk_name3_sei_paren' => null,

                'tk_name4_sei' => $participants[3]['last_name'] ?? null,
                'tk_name4_mei' => $participants[3]['first_name'] ?? null,
                'tk_name4_mei_paren' => $participants[3]['furigana_old_last_name_age'] ?? null,
                'tk_name4_katagaki' => $participants[3]['furigana_title'] ?? null,
                'tk_name4_sei_ruby' => implode('；', $participants[3]['last_name_furigana'] ?? []) ?? null,
                'tk_name4_mei_ruby' => implode('；', $participants[3]['first_name_furigana'] ?? []) ?? null,
                'tk_name4_sei_paren' => null,

                'tk_name5_sei' => $participants[4]['last_name'] ?? null,
                'tk_name5_mei' => $participants[4]['first_name'] ?? null,
                'tk_name5_mei_paren' => $participants[4]['furigana_old_last_name_age'] ?? null,
                'tk_name5_katagaki' => $participants[4]['furigana_title'] ?? null,
                'tk_name5_sei_ruby' => implode('；', $participants[4]['last_name_furigana'] ?? []) ?? null,
                'tk_name5_mei_ruby' => implode('；', $participants[4]['first_name_furigana'] ?? []) ?? null,
                'tk_name5_sei_paren' => null,

                'tk_name6_sei' => $participants[5]['last_name'] ?? null,
                'tk_name6_mei' => $participants[5]['first_name'] ?? null,
                'tk_name6_mei_paren' => $participants[5]['furigana_old_last_name_age'] ?? null,
                'tk_name6_katagaki' => $participants[5]['furigana_title'] ?? null,
                'tk_name6_sei_ruby' => implode('；', $participants[5]['last_name_furigana'] ?? [])  ?? null,
                'tk_name6_mei_ruby' => implode('；', $participants[5]['first_name_furigana'] ?? [])  ?? null,
                'tk_name6_sei_paren' => null,

                'mocyu_sendmonth' => $mourmingCard->month_send ?? null,
                'mocyu1_zokugara' => $mourmingCard->first_person_relation ?? null,
                'mocyu1_name' => trim(($mourmingCard->first_person_last_name ?? '') . ' ' . ($mourmingCard->first_person_first_name ?? '')) ?? null,
                'mocyu1_mm' => $mourmingCard->first_person_month ?? null,
                'mocyu1_dd' => $mourmingCard->first_person_day ?? null,
                'mocyu1_kyonen' => $mourmingCard->first_person_age ?? null,
                'mocyu2_zokugara' => $mourmingCard->second_person_relation ?? null,
                'mocyu2_name' => trim(($mourmingCard->second_person_last_name ?? '') . ' ' . ($mourmingCard->second_person_first_name ?? '')) ?? null,
                'mocyu2_mm' => $mourmingCard->second_person_month ?? null,
                'mocyu2_dd' => $mourmingCard->second_person_day ?? null,
                'mocyu2_kyonen' => $mourmingCard->second_person_age ?? null,
                'mocyu1_text' => null,
                'mocyu2_text' => null,
                'hosoku' => null,
                'tuisin' => null,
                'free_text' => null,
                'sendmonth' => null,
                'kahen_tuki' => null,
                'kahen_1' => null,
                'kahen_2' => null,
                'kahen_3' => null,
                'kahen_4' => null,
                'kahen_5' => null,
                'kahen_6' => null,
                'kousei_flg' => $exchange['kousei_flg'] ?? 0,
                'warimasi' => null,
                'ornament' => null,
                'bolt_type' => null,
                'e_mail1_kigo' => $card->email_url[0]['key'] ?? null,
                'e_mail2_kigo' => $card->email_url[1]['key'] ?? null,
                'chg_zipmark' => null,
                'chg_zip_1' => null,
                'chg_zip_2' => null,
                'chg_add1' => null,
                'chg_add2' => null,
                'chg_add3' => null,
                'chg_sei' => null,
                'chg_sei_kana' => null,
                'chg_sei_par' => null,
                'chg_mei1' => null,
                'chg_mei1_kana' => null,
                'chg_mei1_par' => null,
                'chg_mei2' => null,
                'chg_mei2_kana' => null,
                'chg_mei2_par' => null,
                'size_name' => 0,
                'size_corp' => 0,
                'base_x' => 0,
                'base_y' => 0,
                'text_x' => 0,
                'text_y' => 0,
                'name_sp' => 0,
                'dl_sale_flg' => 0,
                'taiwa_hope_flg' => check_err_dm_new($otherInfo) ? 1 : null,
                'web_1gyo_3mei' => 0,
                'kakko_center' => 0,
                'adr_hyphen_2bu' => 0,
                'calendar_1' => null,
                'calendar_2' => null,
                'calendar_3' => null,
                'calendar_4' => null,
                'calendar_5' => null,
                'calendar_6' => null,
                'photo_image' => null,
                'pic_x' => null,
                'pic_y' => null,
                'pic_width' => null,
                'pic_degree' => 0,
                'photo_image2' => null,
                'pic_x2' => null,
                'pic_y2' => null,
                'pic_width2' => null,
                'pic_degree2' => 0,
                'photo_image3' => null,
                'pic_x3' => null,
                'pic_y3' => null,
                'pic_width3' => null,
                'pic_degree3' => 0,
                'photo_image4' => null,
                'pic_x4' => null,
                'pic_y4' => null,
                'pic_width4' => null,
                'pic_degree4' => 0,
                'photo_image5' => null,
                'pic_x5' => null,
                'pic_y5' => null,
                'pic_width5' => null,
                'pic_degree5' => 0,
                'photo_image6' => null,
                'pic_x6' => null,
                'pic_y6' => null,
                'pic_width6' => null,
                'pic_degree6' => 0,
                'photo_image7' => null,
                'pic_x7' => null,
                'pic_y7' => null,
                'pic_width7' => null,
                'pic_degree7' => 0,
                'photo_image8' => null,
                'pic_x8' => null,
                'pic_y8' => null,
                'pic_width8' => null,
                'pic_degree8' => 0,
                'cx' => null,
                'cy' => null,
                'cw' => null,
                'ch' => null,
                'cx2' => null,
                'cy2' => null,
                'cw2' => null,
                'ch2' => null,
                'appendix' => null,
                'photo_profile' => null,
                'ivory_address' => null,
                'stick_support' => null,
                'kousei_email' => $exchange['kousei_email'] ?? null,
                'red_kbn' => 2,
                'accept_preview' => ((isset($otherInfo['dm_defect_flg']) && $otherInfo['dm_defect_flg'] == 1) && session()->get("no_update_$card->hash_id") || $exchange['accept_preview'] == 0 || session()->has("error_continue_$card->hash_id")) ? 0 : 1,
                'no_deal_reason' => $exchange['no_deal_reason'] ?? null,
                'photo_print_support' => 1,
                'flash_status' => 1,
                'yutai_sw' => 0,
                'kojin_hojin_flg' => 0,
                'cart_access_cnt' => 0,
                'material_id' => $card->material_id,
                'design_id' => $card->product_code,
                'special_no' => 0,
                'dm_data_edit_flg' => $card->style['dm_data_edit_flg'] ?? null,
                'kumihan_err_kbn' => $kumihanError,
                'name2_katagaki' => null,
                'name2_sei' => null,
                'name2_sei_ruby' => null,
                'name3_katagaki' => null,
                'name3_sei' => null,
                'name3_sei_ruby' => null,
                'name4_katagaki' => null,
                'name4_sei' => null,
                'name4_sei_ruby' => null,
                'name5_katagaki' => null,
                'name5_sei' => null,
                'name5_sei_ruby' => null,
                'name6_katagaki' => null,
                'name6_sei' => null,
                'name6_sei_ruby' => null,

                'thumbnail_url' => route('cards.get_image_preview', ['kumihanId' => $card->kumihan_id]),
                'check_support_atena' => $checkSupportAtena,
                'atena_kensu' => $card->address_print_num ?? 0,
                'kumihan_id' => $card->kumihan_id,
                'kumihan_count' => $card->edit_count_complete,
                'font_color' => $card->style['font_color'] ?? null,
                'atena_kumihan_id' => $card->address_print_num ? $card->atena_kumihan_id : null,
                'atena_kumihan_count' => $card->atena_kumihan_count_complete,
                'edit_move_flg' => $card->hasMovedFrames(),
                'senddate' => null,
                'sendtime_id' => null,
                'coupon_cd' => null,
                'tsuika_base_no' => null,
                'user_id' => $userId,

                'dm_number' => $card->dm_info['dm_number'] ?? null,
                'is_virtual_user' => $virtualUser && !$virtualUser->is_real,
                'other_info' => !empty($otherInfo) ? true : false,
                'oname_sei' => $otherInfo['oname_sei'] ?? null,
                'oname_mei' => $otherInfo['oname_mei'] ?? null,
                'req_name_sei_kana' => $otherInfo['req_name_sei_kana'] ?? null,
                'req_name_mei_kana' => $otherInfo['req_name_mei_kana'] ?? null,
                'oemail_new' => $otherInfo['oemail'] ?? null,
                'tok_cd_new' => $otherInfo['tok_cd'] ?? null,
                'owatashi_method' => $otherInfo['owatashi_method'] ?? null,
                'split_tel_1' => $addresses[0]['phone_fax'][0]['phone_1'] ?? null,
                'split_tel_2' => $addresses[0]['phone_fax'][0]['phone_2'] ?? null,
                'split_tel_3' => $addresses[0]['phone_fax'][0]['phone_3'] ?? null,
                'is_simple_flow' => session()->has("is_simple_flow_$card->hash_id") ? session()->get("is_simple_flow_$card->hash_id") : 0,
                'sp_app_send_key' => $card->sp_app_send_key ?? null,
                'sp_app_get_key' => $card->sp_app_get_key ?? null,
            ],
        ]);

        Log::debug('Cart data.', [
            'kumihan_id' => $card->kumihan_id,
            'data' => session()->get("cart_{$card->kumihan_id}"),
        ]);
    }

    private function formatPhoneNumber($phoneNumber)
    {
        if (!$phoneNumber) {
            return '';
        }
        $convertHalfWidth = mb_convert_kana($phoneNumber, 'asKV', 'UTF-8');
        $convertHyphen = preg_replace('/[\s()]+/', '-', $convertHalfWidth);
        $normalizeHyphens = preg_replace('/-{2,}/', '-', $convertHyphen);

        return $normalizeHyphens;
    }

    private function getDtelno($dtelno = null)
    {
        $formatted = $this->formatPhoneNumber($dtelno);

        if (!$this->isHyphenOrDigitsOnly($formatted)) {
            return '';
        }

        return $formatted;
    }

    private function isHyphenOrDigitsOnly($str)
    {
        if (is_null($str)) {
            return false;
        }

        if (preg_match('/[^0-9\-]/', $str)) {
            return false;
        }

        if (strpos($str, '-') === false) {
            return false;
        }

        return true;
    }

    private function getOtelno($otelno = null)
    {
        $formatted = $this->formatPhoneNumber($otelno);

        if (!$this->isHyphenOrDigitsOnly($formatted)) {
            return ['', '', ''];
        }

        $formattedOtelno = explode('-', $formatted);

        return [
            $formattedOtelno[0] ?? '',
            $formattedOtelno[1] ?? '',
            $formattedOtelno[2] ?? '',
        ];
    }

    public function getKumihanError($exchange, $hashId)
    {
        $kumihanErr = config('common.kumihan_err_kbn.default');
        $noDealReason = $exchange['no_deal_reason'] ?? null;
        $kouseiEmail = $exchange['kousei_email'] ?? null;
        $error = session()->get("kumihan_error_$hashId")['kumihan_error'] ?? null;

        if ($noDealReason && $kouseiEmail) {
            $kumihanErr = config('common.kumihan_err_kbn.kousei_email_input');
        } elseif ($noDealReason) {
            $kumihanErr = config('common.kumihan_err_kbn.edit_reason_input');
        } elseif ($error) {
            $kumihanErr = config('common.kumihan_err_kbn.special_error');
        }

        return $kumihanErr;
    }

    public function getCardFolder($card, $configcard = false)
    {
        return get_card_folder($card);
    }


}
