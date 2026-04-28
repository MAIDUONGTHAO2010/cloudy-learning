<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\AgencyRepository;
use App\Http\Requests\Card\CardRequest;
use App\Models\TbTaxInfo;
use App\Services\AgencyService;
use App\Services\AreaAgencyService;
use App\Services\CardService;
use App\Services\CustomerDetailService;
use App\Services\CustomerService;
use App\Services\UserDetailService;
use App\Services\VDesignMService;
use App\Traits\HasLoggedUser;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Card;
use App\Services\ECSiteService;
use App\Services\KumihanService;
use App\Services\ContactService;
use App\Services\MourningCardService;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;
use Illuminate\Support\Facades\Redis;
use App\Contracts\Repositories\TbDesignRepository;
use App\Contracts\Repositories\Mall3OrderItemRepository;

class CardController extends BaseController
{
    use HasLoggedUser;

    protected $service;

    protected $ecSiteService;

    protected $kumihanService;

    protected $contactService;

    protected $mourningCardService;

    protected $vDesignMService;

    protected $customerService;

    protected $customerDetailService;

    protected $agencyService;

    protected $areaAgencyService;

    protected $tbDesignRepository;

    protected $mall3OrderItemRepository;

    /**
     * @var UserDetailService
     */
    protected $userDetailService;

    public function __construct(
        CardService $service,
        ECSiteService $ecSiteService,
        KumihanService $kumihanService,
        VDesignMService $vDesignMService,
        ContactService $contactService,
        CustomerService $customerService,
        CustomerDetailService $customerDetailService,
        MourningCardService $mourningCardService,
        AgencyService $agencyService,
        UserDetailService $userDetailService,
        AreaAgencyService $areaAgencyService,
        TbDesignRepository $tbDesignRepository,
        Mall3OrderItemRepository $mall3OrderItemRepository,
    ) {
        $this->service = $service;
        $this->ecSiteService = $ecSiteService;
        $this->kumihanService = $kumihanService;
        $this->vDesignMService = $vDesignMService;
        $this->contactService = $contactService;
        $this->customerService = $customerService;
        $this->customerDetailService = $customerDetailService;
        $this->mourningCardService = $mourningCardService;
        $this->agencyService = $agencyService;
        $this->userDetailService = $userDetailService;
        $this->areaAgencyService = $areaAgencyService;
        $this->tbDesignRepository = $tbDesignRepository;
        $this->mall3OrderItemRepository = $mall3OrderItemRepository;
    }

    public function selectPhoto($hashid)
    {
        $sessionId = get_session_id();
        $card = $this->service->findCardByHashId($hashid);
        if (!has_photo_frame($card)) {
            return redirect()->back();
        }

        if (session()->has("card_session_$hashid")) {
            $cardSession = session()->get("card_session_$hashid");
            $cardTexts = get_card_texts($cardSession['elements']);
            $cardImages = $card->getImageElements()->toArray();

            if (isset($cardSession['user_photo'])) {
                $cardImages = $cardSession['user_photo'];
                $now = Carbon::now()->timestamp;
                foreach ($cardImages as &$image) {
                    $image['image_url'] = generate_s3_link(get_card_folder($card) . $image['image'], config('card.driver'));
                    if (Storage::disk(config('card.driver'))->exists(get_card_folder($card) . "$sessionId/" . $image['image'])) {
                        $image['image_url'] = generate_s3_link(get_card_folder($card) . "$sessionId/" . $image['image'], config('card.driver'));
                    }
                }
            }

            $cardSession['elements'] = array_merge(
                $cardTexts,
                $card->getClipElements()->toArray(),
                $cardImages,
            );

            $cardSession['total_clip'] = $card->total_clip;
            $card = (object) $cardSession;
        } else {
            $card->load([
                'addresses',
                'participants',
                'company',
                'elements',
            ]);
        }
        $align = $card->style['align'];

        return view('cards.upload', compact('card', 'align'));
    }

    public function edit(Request $request, string $hashId)
    {
        $card = $this->service->findCardByHashId($hashId);

        $frameOutsite = false;
        if (session()->has("card_outsite_$hashId")) {
            $frameOutsite = session()->get("card_outsite_$hashId");
            session()->forget("card_outsite_$hashId");
        }

        $mourningCard = $card->mourningCard()->first() ?? null;
        $newYearCard = $card->newYearCard()->first() ?? null;
        $firstMocyu = $mourningCard->first_mocyu ?? null;
        if ($card->is_free_design) {
            return redirect()
                ->route('free_design.edit', $hashId);
        }

        if (session()->has("card_session_$hashId")) {
            $card = (object) session()->get("card_session_$hashId");
        } else {
            $card->load([
                'addresses',
                'participants',
                'company',
                'elements',
            ]);
        }

        $updateKumihan = false;
        if ($request->get('update_kumihan')) {
            $updateKumihan = true;
        }

        $isMochuu = is_mochuu($card->style['item_kbn']);
        $isKanChuu = is_kanchuu($card->style['item_kbn']);
        $isYoKan = is_yokan($card->style['item_kbn'], $card->agency_id);
        $userDetails = [];

        if ($isMochuu) {
            $sampleTexts = $this->service->getSampleTextsMourning();
        } elseif ($isKanChuu) {
            $sampleTexts = $this->service->getSampleTextsKanchuu();
        } else {
            $sampleTexts = $this->service->getSampleTexts();
        }

        $imageParameters = $this->service->getImageParametersFromECCube();
        if ($user = auth_user($card->agency_id)) {
            $userDetails = $this->userDetailService->getListDetail($user->id);
        }

        if (!session()->has('redirectFromMyPage')) {
            session(['redirectFromMyPage' => true]);
        }

        return view('cards.create', compact(
            'card',
            'sampleTexts',
            'imageParameters',
            'isMochuu',
            'isKanChuu',
            'isYoKan',
            'mourningCard',
            'firstMocyu',
            'newYearCard',
            'userDetails',
            'updateKumihan',
            'frameOutsite',
        ));
    }

    public function redirectPreview(string $hashId)
    {
        $card = $this->service->findCardByHashId($hashId);
        $agencyId = $card->agency_id;
        session()->put("no_update_$hashId", 1);
        $sessionUpdateFlg = session()->get("no_update_$hashId");
        $mourningCard = $card->mourningCard()->first() ?? null;
        $newYearCard = $card->newYearCard()->first() ?? null;
        $firstMocyu = $mourningCard->first_mocyu ?? null;
        if ($card->is_free_design) {
            return redirect()->route('free_design.edit', $hashId);
        }
        if (session()->has("card_session_$hashId")) {
            $card = (object) session()->get("card_session_$hashId");
        } else {
            $card->load([
                'addresses',
                'participants',
                'company',
                'elements',
            ]);
        }
        $isMochuu = is_mochuu($card->style['item_kbn']);
        $isKanChuu = is_kanchuu($card->style['item_kbn']);
        $isYoKan = is_yokan($card->style['item_kbn'], $card->agency_id);
        $userDetails = [];

        if ($isMochuu) {
            $sampleTexts = $this->service->getSampleTextsMourning();
        } elseif ($isKanChuu) {
            $sampleTexts = $this->service->getSampleTextsKanchuu();
        } else {
            $sampleTexts = $this->service->getSampleTexts();
        }

        $imageParameters = $this->service->getImageParametersFromECCube();
        if ($user = auth_user($card->agency_id)) {
            $userDetails = $this->userDetailService->getListDetail($user->id);
        }

        return view('cards.redirect_preview', compact(
            'card',
            'sampleTexts',
            'imageParameters',
            'isMochuu',
            'isKanChuu',
            'isYoKan',
            'mourningCard',
            'firstMocyu',
            'newYearCard',
            'userDetails',
            'sessionUpdateFlg',
            'agencyId',
        ));
    }

    /**
     * @param Request $request
     * @param $hashid
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function update(Request $request, $hashid)
    {
        $card = $this->service->findCardByHashId($hashid);
        $agency = app(AgencyRepository::class)->findAgencyById($card->agency_id);
        if (!$this->service->isSessionTimeout($hashid)) {
            return response()->json([
                'status' => false,
                'code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'SESSION_TIMEOUT',
                'top_page' => config('ecsite.base_url') . "/$agency->agency_ename/$agency->default_area",
            ]);
        }
        try {
            $input = $request->except(['_token', '_method', '_url']);
            //sort participants when enter first_name_furigana, last_name_furigana in the wrong order
            if (isset($input['participants'])) {
                ksort($input['participants']);
            }
            if ($input['input_sender'] == config('card.change_input_sender')) {
                $input['style']['isTypeSetting'] = true;
                if ($input['edit_mode'] == 1) {
                    $cardPreviewSession = (object) session()->get("card_session_preview_$hashid");
                    if ($cardPreviewSession != null) {
                        $checkCardSession = (object) session()->get("card_session_$hashid");
                        if (!check_card_session_preview($cardPreviewSession, $checkCardSession)) {
                            $input['edit_mode'] = 0;
                        }
                    }
                }
            }
            if ($input['textno_change']) {
                $input['style']['text-align'] = '';
            }

            if (!$input['edit_mourning']) {
                unset($input['mourning']);
            } else {
                if (array_key_exists('session_update_flg', $input['mourning'])) {
                    unset($input['mourning']['session_update_flg']);
                }
            }
            $input['moved_frames'] = $request->input('moved_frames', []);

            $mourningCard = $card->mourningCard ?? null;
            $greetingText = '';
            if ($mourningCard) {
                $greetingText = $mourningCard->greeting_text;
            }

            $card->load([
                'addresses',
                'participants',
                'company',
                'elements',
            ]);
            $cardSession = session()->get("card_session_$hashid", $card->toArray());
            $sampleTexts = null;
            if (isset($input['textno_new'])) {
                if (!empty($input['greeting_message'])) {
                    $sampleTexts = $this->service->getSampleTextByValue($input['greeting_message']);
                }

                $input['style']['textno_new'] = $sampleTexts ? $sampleTexts[0]->name : $input['textno_new'];
            }

            $cardSession['style'] = array_merge($cardSession['style'], $input['style']);
            unset($input['style']);
            $cardSession = array_merge($cardSession, $input);
            if (!isset($input['addresses'][1])) {
                unset($cardSession['addresses'][1]);
            }
            if ($input['input_sender']) {
                if (!isset($input['furigana_old_last_name_age'])) {
                    unset($cardSession['furigana_old_last_name_age']);
                }
                if (!isset($input['furigana_title'])) {
                    unset($cardSession['furigana_title']);
                }
                if (!isset($input['participants'])) {
                    unset($cardSession['participants']);
                }
                if (!isset($input['company'])) {
                    unset($cardSession['company']);
                }

                if (!isset($input['last_name_furigana'])) {
                    unset($cardSession['last_name_furigana']);
                }

                if (!isset($input['first_name_furigana'])) {
                    unset($cardSession['first_name_furigana']);
                }
            }

            if ($cardSession['style']['isTypeSetting'] == false
            && $input['input_sender'] != config('card.change_input_sender')) {
                $defaultData = is_mochuu($card->style['item_kbn'])
                    ? config('common.default_customer.mochuu')
                    : config('common.default_customer.newyear');
                $mourningWidwinterFlg = $this->tbDesignRepository->getMidwinterFlg($card->material_id, $card->product_code);
                $midwinterFlg = $mourningWidwinterFlg->midwinter_flg;
                if ($midwinterFlg) {
                    $defaultData['last_name'] = '寒中';
                }

                $sampleSenderInfo = array_merge($cardSession, $defaultData);
                $data = $this->kumihanService->create($sampleSenderInfo, $card);

                if ((is_mochuu($card->style['item_kbn']) || is_kanchuu($card->style['item_kbn'])) && $sampleSenderInfo['edit_data']) {
                    $position = $card['style']['text_ty'] === 'T' ? 'vertical' : 'horizontal';
                    $sampleSenderInfo['edit_data'] = cleateElementKumihanMovedData($data, $position, $sampleSenderInfo, $card);
                    $data = $this->kumihanService->create($sampleSenderInfo, $card);
                }
            } else {
                $data = $this->kumihanService->create($cardSession, $card);

                if ((is_mochuu($card->style['item_kbn']) || is_kanchuu($card->style['item_kbn'])) && $cardSession['edit_data']) {
                    $position = $card['style']['text_ty'] === 'T' ? 'vertical' : 'horizontal';
                    $cardSession['edit_data'] = cleateElementKumihanMovedData($data, $position, $cardSession, $card);
                    $data = $this->kumihanService->create($cardSession, $card);
                }
            }
            if ($data['status'] == 'success') {
                $cardSession['elements'] = $data['elements'];
                session(["card_session_$hashid" => $cardSession]);
                $cardStyleEditCount = $card->style['edit_count'] ?? 0;
                $card['style->edit_count'] = $cardStyleEditCount
                + ((isset($input['edit_mode']) && !$input['edit_mode']) ? 1 : 0);

                $card->update_info = config('card.update_info_complete');

                if (!$input['edit_mode']
                && $input['input_sender'] == config('card.change_input_sender')) {
                    $card->reset_flag = config('card.reset_flag_with_typesetting');
                    if (session()->get("no_update_$hashid") !== null && !session()->get("no_update_$hashid")) {
                        $card['style->dm_data_edit_flg'] = 2;
                    }
                } elseif ($card->reset_flag === config('card.no_reset_flag')) {
                    $card->reset_flag = config('card.reset_flag_without_typesetting');
                }

                $card->preview_complete = config('card.preview_incomplete');
                if ($input['textno_change']) {
                    $sampleTextElement = collect($data['elements'] ?? [])->firstWhere('image', '1TXT.png');

                    $greetingTextElements = collect($data['elements'] ?? [])->whereIn(
                        'image',
                        array_map(function ($item) {
                            return $item . '.png';
                        }, config('card.mochuu_frames')),
                    );

                    if ($mourningCard) {
                        $greetingText = $this->mourningCardService
                            ->getGreetingText($mourningCard, $greetingTextElements);
                    }

                    $cardSession['style']['text_no_content'] = $sampleTextElement['style']['text'] ?? '';
                }
                $card->save();

                if (!is_mochuu($card->style['item_kbn'])) {
                    $card->newYearCard()->update([
                        'calendar' => $request->newyear['calendar'] ?? null,
                    ]);
                }

                $cardSession['update_info'] = $card->update_info;
                $cardSession['reset_flag'] = $card->reset_flag;
                $cardSession['preview_complete'] = $card->preview_complete;
                session(["card_session_$hashid" => $cardSession]);
            } else {
                return $this->responseErrors($data['code'], $data['message'], 200, $data['isSpecialError']);
            }

            if (session()->has("card_session_$hashid")) {
                $card = (object) session()->get("card_session_$hashid");
            }

            return $this->responseSuccess([
                'card' => $card,
                'greetingText' => $greetingText,
            ]);
        } catch (Exception $e) {
            Log::error($e);

            return $this->responseErrors(Response::HTTP_BAD_REQUEST, __('kumihan.common_message'));
        }
    }

    public function reOpenCard(Request $request, $typesettingId, $orderType)
    {
        if ($request->has('ec_ss_id')) {
            session(['ec_ss_id' => $request->input('ec_ss_id', null)]);
        }

        $backUrl = $request->get('back_url');
        $backHost = get_back_host($backUrl);

        try {
            $card = $this->service->findCardByKumihanId($typesettingId);
            $card->update([
                'kumihan_id_temp' => generateKumihanId($card),
            ]);

            $title = '編集画面';
            // Call api copy kumihan
            $kumihanCopyCode = $this->kumihanService->copyKumihan($card);
            if ($kumihanCopyCode !== 'OK') {
                $redirectUrl = $backUrl ?? env('ECSITE_URL');
                $popupMessageType = config('kumihan.kumihan_copy.popup_message_type.cart');
                return view('errors.kumihan-copy-error', compact('kumihanCopyCode', 'redirectUrl', 'popupMessageType', 'title'));
            }

            //            $cartToken = $this->ecSiteService->authorize($card, $cartId);
            $hashid = hash_card_id($card->id);
            $card->update([
                'back_url' => $backUrl,
                'add_order_flg' => $orderType,
                'back_host' => $backHost,
                //                'cart_token' => $cartToken,
                //                'cart_id' => $cartId,
                'update_info' => config('card.update_info_incomplete'),
                'preview_complete' => config('card.preview_incomplete'),
                'reset_flag' => config('card.reset_flag_with_typesetting'),
            ]);

            $card->forceFill([
                'style->edit_count' => !$card->edit_count_complete
                    ? $card->style['edit_count'] : $card->edit_count_complete,
            ])->save();

            $card->load([
                'addresses',
                'participants',
                'company',
                'elements',
            ]);
            session(["card_session_$hashid" => $card->toArray()]);

            if ($card->style['photo_flg'] != Card::HAS_PHOTO_FLG || $card->is_free_design) {
                return redirect()->route('cards.edit', hash_card_id($card->id));
            }

            return redirect()->route('cards.selectPhoto', hash_card_id($card->id));
        } catch (Exception $e) {
            Log::error($e);

            abort(404);
        }
    }

    public function replicateCard($cartId, $typesettingId)
    {
        try {
            $card = $this->service->findCardByKumihanId($typesettingId);
            $cartToken = $this->ecSiteService->authorize($card, $cartId);

            $card->update([
                'cart_token' => $cartToken,
                'cart_id' => $cartId,
            ]);

            return redirect()->route('cards.redirect_ecsite', [
                'hashid' => $card->hash_id,
                'redirectPage' => 'cart_return',
            ]);
        } catch (Exception $e) {
            Log::error($e);

            abort(404);
        }
    }

    public function create($cartId, $productCode, $productId, $shopKey, $orderType, $typesettingId = null)
    {
        $backUrl = url()->previous();
        $backHost = get_back_host($backUrl);

        if (app()->environment('staging', 'production') && $backHost == config('app.url')) {
            Log::notice('Not request from ECSite', [
                'cartId' => $cartId,
                'productCode' => $productCode,
                'productId' => $productId,
                'shopKey' => $shopKey,
                'orderType' => $orderType,
                'typesettingId' => $typesettingId,
            ]);

            abort(404);
        }

        if (!in_array($orderType, config('common.order_types'))) {
            Log::notice('wrong orderType.', [
                'cartId' => $cartId,
                'productCode' => $productCode,
                'productId' => $productId,
                'shopKey' => $shopKey,
                'orderType' => $orderType,
                'typesettingId' => $typesettingId,
            ]);

            abort(404);
        }
        if ($orderType != config('common.order_types.create') && empty($typesettingId)) {
            Log::notice('wrong orderType && typesettingId.', [
                'cartId' => $cartId,
                'productCode' => $productCode,
                'productId' => $productId,
                'shopKey' => $shopKey,
                'orderType' => $orderType,
                'typesettingId' => $typesettingId,
            ]);

            abort(404);
        }

        if ($orderType == config('common.order_types.replicate') && !empty($typesettingId)) {
            return $this->replicateCard($cartId, $typesettingId);
        }

        if (!empty($typesettingId)) {
            // reOpenCard expects a Request as first argument — pass current request context
            return $this->reOpenCard(request(), $typesettingId, $orderType);
        }

        $vDesignM = $this->vDesignMService->findByDesignNo($productCode);

        $cardStyle = [
            'typeno' => $vDesignM->dtype,
            'text_ty' => $vDesignM->kumi_ty,
            'allow_decoration_stamp' => $vDesignM->allow_decoration_stamp,
            'designcolor_name' => $vDesignM->designcolor_name,
            'designcolor_cd' => $vDesignM->designcolor_cd,
            'photo_flg' => $vDesignM->photo_flg,
            'printedmount' => $vDesignM->printedmount,
            'text_no' => $vDesignM->kumi_txt_no,
            'kumi_txt_no' => $vDesignM->kumi_txt_no, // default
            'pldptmng_no_str' => $vDesignM->pldptmng_no_str,
            'design_ty' => $vDesignM->design_ty,
            'edit_mode' => 0,
            'edit_count' => 0,
            'kumihan_count' => 0,
            'shotai' => $vDesignM->kumi_font_cd,
            'font_color' => filterFontColor($vDesignM),
            'han_color_k' => $vDesignM->han_color_k,
            'outline_char_kbn' => $vDesignM->outline_char_kbn,
            'isTypeSetting' => false,
            'photo_finishing_flg' => $vDesignM->photo_finishing_flg,
            'yoridori_kbn' => $vDesignM->yoridori_kbn,
            'item_kbn' => $vDesignM->item_kbn,
            'free_edit_flg' => $vDesignM->free_edit_flg,
            'kumi_atena_faceprt_flg' => $vDesignM->kumi_atena_faceprt_flg,
            'dm_data_edit_flg' => null,
        ];

        $isMochuu = is_mochuu($vDesignM->item_kbn);
        $isKanChuu = is_kanchuu($vDesignM->item_kbn);

        if ($vDesignM->design_ty == Card::IS_VERTICAL_DESIGN) {
            $cardStyle['width'] = 100;
            $cardStyle['height'] = 148;
            $cardStyle['align'] = 'vertical';
            $cardStyle['card_width_in_px'] = 2362;
            $cardStyle['card_height_in_px'] = 3495;
            $screenCard = config('common.storage_server.free_design.default_screen_card_vertical');
            $printCard = config('common.storage_server.free_design.default_print_card_vertical');
        } else {
            $cardStyle['width'] = 148;
            $cardStyle['height'] = 100;
            $cardStyle['align'] = 'horizontal';
            $cardStyle['card_width_in_px'] = 3495;
            $cardStyle['card_height_in_px'] = 2362;
            $screenCard = config('common.storage_server.free_design.default_screen_card_horizontal');
            $printCard = config('common.storage_server.free_design.default_print_card_horizontal');
        }

        if ($vDesignM->is_free_design) {
            $screenCardExt = File::extension($screenCard);
            $printCardExt = File::extension($printCard);
            $defaultScreenName = sprintf(config('card.default_name.screen'), $screenCardExt);
            $defaultPrintName = sprintf(config('card.default_name.print'), $screenCardExt);

            $card = $this->service->create([
                'image' => $defaultScreenName,
                'print_background' => $defaultPrintName,
                'cart_id' => $cartId,
                'product_code' => $productCode,
                'product_id' => $productId,
                'shop_key' => $shopKey,
                'add_order_flg' => $orderType,
                'kumihan_id' => $typesettingId,
                'style' => $cardStyle,
                'elements' => $elements ?? [],
                'back_url' => $backUrl,
                'back_host' => $backHost,
                'reset_flag' => config('card.no_reset_flag'),
                'user_id' => app()->environment('develop', 'local')
                    ? request()->get('user_id', null) : null,
                'user_login_type' => app()->environment('develop', 'local') && request()->get('user_id')
                    ? config('ecsite.login_types.is_logged_in') : config('ecsite.login_types.not_logged_in'),
            ]);
            $card->newYearCard()->create();

            $cardFolder = $this->service->getCardFolder($card);

            if (!File::isDirectory($cardFolder)) {
                File::makeDirectory($cardFolder, 0777, true, true);
            }

            if (!File::copy($screenCard, $cardFolder . $defaultScreenName)
                || !File::copy($printCard, $cardFolder . $defaultPrintName)) {
                Log::notice('could not copy images from /app/public/images/free-design-bg.', [
                    'defaultScreenName' => $cardFolder . $defaultScreenName,
                    'defaultPrintName' => $cardFolder . $defaultPrintName,
                ]);
                abort(404);
            }

            Log::info('Created new free design card', [
                'kumihan_id' => $card->kumihan_id,
            ]);

            return view('cards.ecsite_to_editsite', compact(
                'card',
                'isMochuu',
            ));
        }

        $masks = [];
        if ($vDesignM->photo_flg == Card::HAS_PHOTO_FLG) {
            $xmlExtendPath = sprintf(config('common.storage_server.extend_xml_path'), $productCode);
            if (!Storage::disk('master_s3')->has($xmlExtendPath)) {
                Log::notice('xmlExtendPath not found.', [
                    'xmlExtendPath' => $xmlExtendPath,
                ]);
                abort(404);
            }

            $elementClips = extractClipFromXml($xmlExtendPath);
            $screenCard = sprintf(config('common.storage_server.master.has_photo.screen_path'), $productCode);
            $printCard = sprintf(config('common.storage_server.master.has_photo.print_path'), $productCode);

            $elements = [];
            foreach ($elementClips as $index => $clip) {
                $maskUrl = sprintf(
                    config('common.storage_server.master.has_photo.mask_path'),
                    $productCode,
                    $clip['mask_id'],
                );
                $maskExt = File::extension($maskUrl);
                $maskName = sprintf(config('card.default_name.mask'), $clip['mask_id'], $maskExt);
                $masks[] = compact('maskUrl', 'maskExt', 'maskName');

                $x1 = $clip['dimensions']['x1'] * 600 / 72 / $cardStyle['card_width_in_px'] * $cardStyle['width'];
                $y1 = $clip['dimensions']['y1'] * 600 / 72 / $cardStyle['card_height_in_px'] * $cardStyle['height'];
                $x2 = ($clip['dimensions']['x2'] * 600 / 72
                    / $cardStyle['card_width_in_px'] * $cardStyle['width']) <= $cardStyle['width']
                    ? ($clip['dimensions']['x2'] * 600 / 72
                    / $cardStyle['card_width_in_px'] * $cardStyle['width']) : $cardStyle['width'];
                $y2 = ($clip['dimensions']['y2'] * 600 / 72
                    / $cardStyle['card_height_in_px'] * $cardStyle['height']) <= $cardStyle['height']
                    ? ($clip['dimensions']['y2'] * 600 / 72
                    / $cardStyle['card_height_in_px'] * $cardStyle['height']) : $cardStyle['height'];
                $elements[] = [
                    'width' => $x2 - $x1,
                    'height' => $y2 - $y1,
                    'x_coordinate' => $x1,
                    'y_coordinate' => $y1,
                    'style' => [
                        'type' => config('card.element_type.clip'),
                        'width' => $x2 - $x1,
                        'height' => $y2 - $y1,
                        'x_coordinate' => $x1,
                        'y_coordinate' => $y1,
                        'clip_type' => $clip['type'],
                        'original_coordinate' => $clip['coordinates'],
                        'mask' => [
                            'name' => $maskName,
                            'id' => $clip['mask_id'],
                        ],
                    ],
                ];
            }
            $cardStyle['total_clip'] = count($elements);
        } else {
            $screenCardName = find_design_screen_image($productCode);
            $screenCard = config('common.storage_server.master.has_not_photo.screen_path') . $screenCardName;
            if (!$screenCardName) {
                Log::notice('images not found.', [
                    'screenCardName' => $screenCardName,
                    'producCode' => $productCode,
                    'screenCard' => $screenCard,
                ]);
                abort(404);
            }
        }

        $screenCardExt = File::extension($screenCard);
        $printCardExt = File::extension($printCard);

        $defaultScreenName = sprintf(config('card.default_name.screen'), $screenCardExt);
        $defaultPrintName = sprintf(config('card.default_name.print'), $printCardExt);

        $card = $this->service->create([
            'image' => $defaultScreenName,
            'print_background' => $defaultPrintName,
            'cart_id' => $cartId,
            'product_code' => $productCode,
            'product_id' => $productId,
            'shop_key' => $shopKey,
            'add_order_flg' => $orderType,
            'kumihan_id' => $typesettingId,
            'style' => $cardStyle,
            'elements' => $elements ?? [],
            'back_url' => $backUrl,
            'back_host' => $backHost,
            'reset_flag' => config('card.no_reset_flag'),
            'user_id' => app()->environment('develop', 'local')
                ? request()->get('user_id', null) : null,
            'user_login_type' => app()->environment('develop', 'local') && request()->get('user_id')
                ? config('ecsite.login_types.is_logged_in') : config('ecsite.login_types.not_logged_in'),
        ]);

        if (!is_mochuu($cardStyle['item_kbn'])) {
            $card->newYearCard()->create();
        }

        $cardFolder = $this->service->getCardFolder($card);

        if (!File::isDirectory($cardFolder)) {
            File::makeDirectory($cardFolder, 0777, true, true);
        }

        if (!File::copy($screenCard, $cardFolder . $defaultScreenName)
            || !File::copy($printCard, $cardFolder . $defaultPrintName)) {
            Log::notice('could not copy images from /mnt/editsite.', [
                'defaultScreenName' => $cardFolder . $defaultScreenName,
                'defaultPrintName' => $cardFolder . $defaultPrintName,
            ]);
            abort(404);
        }

        foreach ($masks as $mask) {
            if (!File::copy($mask['maskUrl'], $cardFolder . $mask['maskName'])) {
                Log::notice('could not copy images from /mnt/editsite.', [
                    'defaultMaskName' => $cardFolder . $mask['maskName'],
                ]);
                abort(404);
            }
        }

        Log::info('Created new card', [
            'kumihan_id' => $card->kumihan_id,
        ]);

        return view('cards.ecsite_to_editsite', compact(
            'card',
            'isMochuu',
        ));
    }

    /**
     * Get user's naire info
     *
     * @param Request $request
     * @param string $hashId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartInfo(Request $request, string $hashId)
    {
        try {
            $card = $this->service->findCardByHashId($hashId);
            $isMochuu = is_mochuu($card->style['item_kbn']);
            $user = auth_user($card->agency_id);
            $userDetails = collect();
            $senderInfo = [];
            $offset = $request->get('offset') ?? 0;
            $isNullSenderInfo = false;

            //For the first load naire data
            // only_db: only get and show naire information from db
            // $card->dm_info['user_id'] = false : naire info does not save to user detail yet
            $showChangeCompanyInfoModal = false;
            if (!$request->get('only_db') && !$card->style['allow_corporate_use'] && $card->dm_info) {
                $showChangeCompanyInfoModal = true;
            }
            if (!$request->get('only_db') && empty($card->dm_info['user_id'])) {
                $senderInfo = $card->dm_info;
            }
            $isCallApi2 = false;
            $numSenderInfo = 0 ;
            $isUpdateUserDetail = false;
            $imageUrl = '';
            if ($user) {
                if (session()->has("is_with_dm_new_$hashId")) {
                    $isCallApi2 = true;

                    $dmInfoNew = session()->get("dm_info_new");
                    $numSenderInfo = $dmInfoNew['numSenderInfo'] ?? 0;

                }
                if (session()->has("is_update_user_detail_$hashId")) {
                    $isUpdateUserDetail = true;
                    $imageDomain = config('common.dm_image_naire');
                    $dmid = $card->dm_info['dm_number'];
                    $dmpw = $card->dm_info['dm_password'];
                    $imageUrl = "$imageDomain?dmid=$dmid&dmpw=$dmpw";
                }
                // $perPage = $request->get('only_db')
                //     ? config('common.per_page')
                //     : config('common.per_page') - 1; // (-1 for data sender_info session)
                $perPage = config('common.per_page');
                $userDetails = $this->userDetailService->getListDetail($user->id, ['*'], [
                    'offset' => $offset,
                    'per_page' => $perPage,
                ]);

                $newOffset = $userDetails->count() >= $perPage ? $offset + $perPage : null;
            }

            return response()->json([
                'status' => true,
                'num_sender_info' => $numSenderInfo,
                'is_call_api2' => $isCallApi2,
                'image_url' => $imageUrl,
                'is_update_user_detail' => $isUpdateUserDetail,
                'is_has_sender_info' => !empty($senderInfo),
                'is_null_sender_info' => $isNullSenderInfo,
                'is_logged_in' => (bool) $user,
                'count_users' => $userDetails->count(),
                'user_details' => $userDetails,
                'user_details_html' => view('partials.cards.list-senders', compact(
                    'senderInfo',
                    'userDetails',
                    'isMochuu',
                    'card',
                    'offset',
                ))->render(),
                'code' => Response::HTTP_OK,
                'offset' => $newOffset ?? null,
                'show_change_company_info_modal' => $showChangeCompanyInfoModal,
            ]);
        } catch (Throwable $e) {
            Log::error($e);

            return response()->json([
                'status' => false,
                'code' => Response::HTTP_BAD_REQUEST,
            ]);
        }
    }

    public function requestKumihan1stTime(Request $request, $hashid)
    {
        $card = $this->service->findCardByHashId($hashid);
        $result = $this->kumihanService->kumihan1stTime($card);
        $frameOutsite = $request->frame_out_site ? checkFrameOutsiteCard($card) : false;

        if ($frameOutsite) {
            session()->put("card_outsite_$hashid", $frameOutsite);
            session()->put("no_update_$hashid", 0);

            if (!has_photo_frame($card)) {
                return [
                    'status' => true,
                    'code' => 423,
                    'message' => 'Frame text outsite Card layout',
                    'isSpecialError' => false,
                ];
            }
        }

        return response()->json($result);
    }

    public function cartPreview($kumihanId)
    {
        $card = $this->service->findCardByKumihanId(decrypt_nengaorder(base64_decode($kumihanId)));
        $isMochuu = is_mochuu($card->style['item_kbn']);

        return view(
            'cards.cart_preview',
            compact(
                'card',
                'isMochuu',
            ),
        );
    }

    public function preview(Request $request, $hashid)
    {
        $input = $request->all([
            'edit_data',
            'elements',
            'moved_frames',
        ]);
        $isNoUpdate = false;
        if (session()->get("no_update_$hashid")) {
            $input['edit_data'] = '';
            $isNoUpdate = true;
        }
        $card = $this->service->findCardByHashId($hashid);
        $originalKumihanId = $card->kumihan_id;
        // $this->service->copyCardFilesToMountedStorage($card);
        $isMochuu = is_mochuu($card->style['item_kbn']);
        $exchange = $this->service->getSessionExchange($hashid);
        $isKanChuu = is_kanchuu($card->style['item_kbn']);
        $isYoKan = is_yokan($card->style['item_kbn'], $card->agency_id);
        $sessionId = get_session_id();
        $cardSession = session()->get("card_session_$hashid", $card->toArray());
        $updateOrCreateSender = $this->customerDetailService->exists($card->id);
        $checkCardSupportAtena = $this->service->checkSupportAtena([
            'design_id' => $card->product_code,
            'material_id' => $card->material_id,
            'agency_id' => $card->agency_id,
            'site_id' => $card->area_id,
        ], $hashid);
        $checkSupportAtena = $checkCardSupportAtena['has_atena'];
        $isSimpleFlow = (bool) session()->get("is_simple_flow_$hashid", 0);
        $isSimpleFlowBack = session()->get("simple_flow_$hashid");
        $flagExchange = session('flag-exchange');

        if ($request->isMethod('get') && !$isNoUpdate) {
            $previewImage = $this->service->getCardFolder($card) . config('card.image_name.preview') . '.jpg';
            if (Storage::disk(config('card.driver'))->exists($previewImage)) {
                return view(
                    'cards.preview',
                    compact(
                        'card',
                        'isMochuu',
                        'updateOrCreateSender',
                        'originalKumihanId',
                        'checkSupportAtena',
                        'exchange',
                        'isKanChuu',
                        'isYoKan',
                        'isNoUpdate',
                        'isSimpleFlow',
                        'isSimpleFlowBack',
                        'flagExchange',
                    ),
                );
            }

            return redirect()->back();
        }

        try {
            $card->load([
                'addresses',
                'participants',
                'company',
                'elements',
            ]);
            $inputElements = json_decode($input['elements'] ?? '[]', true);
            $cardSession['stamp_photo'] = [];
            if (!empty($inputElements)) {
                foreach ($inputElements as $elementIndex => $element) {
                    if ($element['style']['type'] === config('card.element_type.background')) {
                        if (strpos($element['image'], '/screen.')) {
                            unset($inputElements[$elementIndex]);
                            continue;
                        }
                        $backgroundElement = $card->getBackgroundElement();
                        if (!$backgroundElement || $backgroundElement->image !== $element['image']) {
                            $pathParts = pathinfo($element['image']);
                            $imageName = $pathParts['filename'];
                            if ($card->style['design_ty'] == Card::IS_VERTICAL_DESIGN) {
                                $screenCard = sprintf(
                                    config('common.storage_server.free_design.screen_card_vertical'),
                                    $imageName,
                                );
                                $printCard = sprintf(
                                    config('common.storage_server.free_design.print_card_vertical'),
                                    $imageName,
                                );
                            } else {
                                $screenCard = sprintf(
                                    config('common.storage_server.free_design.screen_card_horizontal'),
                                    $imageName,
                                );
                                $printCard = sprintf(
                                    config('common.storage_server.free_design.print_card_horizontal'),
                                    $imageName,
                                );
                            }
                            $defaultScreenName = sprintf(
                                config('card.default_name.screen'),
                                File::extension($screenCard),
                            );
                            $defaultPrintName = sprintf(
                                config('card.default_name.print'),
                                File::extension($printCard),
                            );

                            $cardFolder = $this->service->getCardFolder($card) . '/' . $sessionId . '/';

                            File::copy($screenCard, $cardFolder . $defaultScreenName);
                            File::copy($printCard, $cardFolder . $defaultPrintName);
                        }
                    } elseif ($element['style']['type'] === config('card.element_type.stamp')) {
                        $cardSession['stamp_photo'][] = $element;
                    }
                }
            }

            $kumihanRequest = $cardSession;
            $kumihanRequest['complete'] = '1';
            $kumihanRequest['edit_data'] = $input['edit_data'];
            $kumihanRequest['edit_mode'] = '1';
            $kumihanRequest['textno_change'] = '0';
            if (empty($card->first_sender_input_data)
                && (
                    $card->style['edit_count'] === 1
                    || $card->reset_flag === config('card.no_reset_flag')
                    || $card->reset_flag === config('card.reset_flag_without_typesetting')
                )
            ) {
                $kumihanRequest['edit_mode'] = '0';
            }

            if (session()->has("card_session_preview_$hashid")) {
                $cardCheck = (object) session()->get("card_session_$hashid");
                $cardPreview = (object) session()->get("card_session_preview_$hashid");
                if (!check_card_session_preview($cardCheck, $cardPreview)) {
                    Log::info('check_card_session_preview', ['card_kumihan_id' => $card->id]);
                    //$kumihanRequest['edit_mode'] = '0';
                }
            }

            $data = $this->kumihanService->create($kumihanRequest, $card);
            if ($data['status'] == 'success') {
                $inputElements = array_merge($inputElements, $cardSession['crop_photo'] ?? []);
                $cardSession['elements'] = array_merge($inputElements, $data['elements']);

                $card['style->edit_count'] = ($card->style['edit_count'] ?? 0)
                    + ((!$kumihanRequest['edit_mode']) ? 1 : 0);
                $card->preview_complete = $cardSession['preview_complete'] = config('card.preview_complete');
                $card['style->isTypeSetting'] = $cardSession['style']['isTypeSetting'] = true;
                $card->reset_flag = $cardSession['reset_flag'] = config('card.reset_flag_with_typesetting');
                $card->save();
            } else {
                $errorMessage = __('kumihan.common_message');

                return back()->withErrors([
                    'errorMessage' => $errorMessage,
                    'isSpecialError' => $data['isSpecialError'],
                ]);
            }

            $cardSession['style'] = array_merge($card->style, $cardSession['style']);
            $cardSession['preview'] = $cardSession['image_preview'] = config('card.image_name.preview') . '.jpg';
            $cardSession['edit_data'] = $input['edit_data'];
            $cardSession['moved_frames'] = $input['moved_frames'] ?? [];
            $isSessionTimeout = session()->pull("cart_session_$hashid") ?? false;

            if ($isSessionTimeout) {
                return view(
                    'cards.preview',
                    compact(
                        'card',
                        'isMochuu',
                        'updateOrCreateSender',
                        'originalKumihanId',
                        'checkSupportAtena',
                        'exchange',
                        'isKanChuu',
                        'isYoKan',
                        'isNoUpdate',
                        'isSimpleFlow',
                        'isSimpleFlowBack',
                        'flagExchange',
                    ),
                );
            }

            session(["card_session_$hashid" => $cardSession]);

            if (session()->has("card_session_$hashid")) {
                $card = (object) session()->get("card_session_$hashid");
            }

            if ($this->service->exportPreview($card->id, $data['path']) && $this->service->exportAtenaPreview($card->id, $data['path'])) {
                session(["card_session_preview_$hashid" => $cardSession]);
                return view(
                    'cards.preview',
                    compact(
                        'card',
                        'isMochuu',
                        'updateOrCreateSender',
                        'originalKumihanId',
                        'checkSupportAtena',
                        'exchange',
                        'isKanChuu',
                        'isYoKan',
                        'isNoUpdate',
                        'isSimpleFlow',
                        'isSimpleFlowBack',
                        'flagExchange',
                    ),
                );
            }
        } catch (Exception $e) {
            Log::error($e);
            return back()->withErrors([
                'message' => __('message.common_error'),
            ]);
        }
    }

    public function getSampleTextByCategoryId($cardId, $categoryId)
    {
        $sampleTexts = $this->service->getSampleTextByCategoryId($categoryId);
        $card = $this->service->findCardById($cardId);

        $isMochuu = is_mochuu($card->style['item_kbn']);
        $isKanChuu = is_kanchuu($card->style['item_kbn']);

        return [
            'html' => view(
                'partials.cards.create.tab-contents.sample-texts.list',
                compact('sampleTexts', 'isMochuu', 'card', 'isKanChuu'),
            )->render(),
        ];
    }

    public function contact(Request $request, $hashid)
    {
        $card = $this->service->findCardByHashId($hashid);

        $params = $this->contactService->getIframeParams($card);

        if ($request->isMethod('post')) {
            $card->update(['contact_complete' => config('card.contact_complete')]);

            if (session()->has("card_session_$hashid")) {
                $cardSession = session()->get("card_session_$hashid");
                $cardSession['contact_complete'] = $card->contact_complete;
                session(["card_session_$hashid" => $cardSession]);
            }
        }

        if ($card->contact_complete == config('card.contact_complete')) {
            return view('cards.contact', compact('card', 'params'));
        }

        return redirect()->back();
    }

    public function contactFinish($hashid)
    {
        $card = $this->service->findCardByHashId($hashid);
        $this->contactService->finish($card);

        return redirect()->route('cards.redirect_ecsite', [
            'hashid' => $card->hash_id,
            'redirectPage' => 'cart_return',
        ]);
    }

    public function deleteText(CardRequest $request, $hashid)
    {
        $card = $this->service->findCardByHashId($hashid);
        $agency = app(AgencyRepository::class)->findAgencyById($card->agency_id);
        if (!$this->service->isSessionTimeout($hashid)) {
            return response()->json([
                'status' => false,
                'code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'SESSION_TIMEOUT',
                'top_page' => config('ecsite.base_url') . "/$agency->agency_ename/$agency->default_area",
            ]);
        }
        try {
            if ($request->input('edit_mode')) {
                $input = $request->all([
                    'text_no',
                    'edit_mode',
                    'style',
                    'edit_data',
                    'input_sender',
                    'textno_change',
                    'textno_new',
                ]);
            } else {
                $input = $request->except(['_token', '_method']);
                if ($input['textno_change']) {
                    $input['style']['text-align'] = '';
                }

                if ($input['input_sender'] == config('card.change_input_sender')) {
                    $input['style']['isTypeSetting'] = true;
                }
            }
            if (isset($input['textno_new'])) {
                $input['style']['textno_new'] = $input['textno_new'];
            }
            $input['moved_frames'] = $request->input('moved_frames', []);

            $mourningCard = $card->mourningCard ?? null;
            $greetingText = '';

            if ($input['input_sender'] == config('card.reset_kumihan')) {
                $vDesignM = $this->vDesignMService->findByDesignNo($card->product_code);
                $input['style']['shotai'] = $vDesignM->kumi_font_cd;
                $input['style']['font_color'] = filterFontColor($vDesignM);
                $input['style']['text-align'] = '';
                $input['style']['isTypeSetting'] = false;
                $input['style']['mojigumi'] = '';
            }

            $card->load([
                'addresses',
                'participants',
                'company',
                'elements',
            ]);
            $cardSession = session()->get("card_session_$hashid", $card->toArray());
            $cardSession['style'] = array_merge($cardSession['style'], $input['style']);
            unset($input['style']);
            if ($input['input_sender']) {
                unset($cardSession['last_name_furigana']);
                unset($cardSession['first_name_furigana']);
                unset($cardSession['furigana_old_last_name_age']);
                unset($cardSession['furigana_title']);
                unset($cardSession['participants']);
                unset($cardSession['company']);
            }

            $cardSession = array_merge($cardSession, $input);
            if ($cardSession['style']['isTypeSetting'] == false
            && $input['input_sender'] != config('card.change_input_sender')) {
                if (!empty($card->first_sender_input_data)) {
                    $defaultData = $card->first_sender_input_data;
                    $cardSession = array_merge($cardSession, $defaultData);
                    $cardSession['style']['isTypeSetting'] = true;
                } else {
                    $defaultData = is_mochuu($card->style['item_kbn'])
                        ? config('common.default_customer.mochuu')
                        : config('common.default_customer.newyear');
                    $mourningWidwinterFlg = $this->tbDesignRepository->getMidwinterFlg($card->material_id, $card->product_code);
                    $midwinterFlg = $mourningWidwinterFlg->midwinter_flg;
                    if ($midwinterFlg) {
                        $defaultData['last_name'] = '寒中';
                    }
                }
                $sampleSenderInfo = array_merge($cardSession, $defaultData);
                $data = $this->kumihanService->create($sampleSenderInfo, $card);
            } else {
                $data = $this->kumihanService->create($cardSession, $card);
            }

            if ($data['status'] == 'success') {
                $cardSession['elements'] = $data['elements'];
                session(["card_session_$hashid" => $cardSession]);
                $cardStyleEditCount = $card->style['edit_count'] ?? 0;
                $card['style->edit_count'] = $cardStyleEditCount
                    + ((isset($input['edit_mode']) && !$input['edit_mode']) ? 1 : 0);
                $card->update_info = config('card.update_info_complete');

                if (!$input['edit_mode']
                && $input['input_sender'] == config('card.change_input_sender')) {
                    $card->reset_flag = config('card.reset_flag_with_typesetting');
                    if (session()->get("no_update_$hashid") !== null && !session()->get("no_update_$hashid")) {
                        $card['style->dm_data_edit_flg'] = 2;
                    }
                } elseif ($card->reset_flag === config('card.no_reset_flag')) {
                    $card->reset_flag = config('card.reset_flag_without_typesetting');
                }

                if ($input['input_sender'] == config('card.reset_kumihan')) {
                    $card->update_info = config('card.update_info_incomplete');
                    $card->reset_flag = config('card.no_reset_flag');
                }

                $card->preview_complete = config('card.preview_incomplete');
                $card->save();

                $cardSession['update_info'] = $card->update_info;
                $cardSession['reset_flag'] = $card->reset_flag;
                $cardSession['preview_complete'] = $card->preview_complete;
                session(["card_session_$hashid" => $cardSession]);
            } else {
                return $this->responseErrors($data['code'], $data['message']);
            }

            if (session()->has("card_session_$hashid")) {
                $card = (object) session()->get("card_session_$hashid");
            }

            if ($mourningCard) {
                $greetingText = $mourningCard->greeting_text;
            }

            return $this->responseSuccess([
                'card' => $card,
                'greetingText' => $greetingText,
            ]);
        } catch (Exception $e) {
            Log::error($e);

            return $this->responseErrors(Response::HTTP_BAD_REQUEST, __('kumihan.common_message'));
        }
    }

    public function back($hashid, $isSelectPhotoScreen = 1)
    {
        $card = $this->service->findCardByHashId($hashid);

        if (!has_photo_frame($card) || $isSelectPhotoScreen) {
            session()->forget("card_session_$hashid");

            return redirect()->away($card->back_url);
        }

        return redirect()->route('cards.selectPhoto', $hashid);
    }

    public function redirectEcSite($hashid, $redirectPage)
    {
        $card = $this->service->findCardByHashId($hashid);
        $productCode = $card->product_code;
        $redirectUrl = back()->getTargetUrl();

        switch ($redirectPage) {
            case 'temporary_cart':
                if ($card->kumihan_id_temp) {
                    // Call api copy kumihan
                    $kumihanCopyCode = $this->kumihanService->copyKumihan($card, $functionMode = 0, $copyMode = 1);
                    if ($kumihanCopyCode !== 'OK') {
                        $popupMessageType = config('kumihan.kumihan_copy.popup_message_type.preview');
                        return view(
                            'errors.kumihan-copy-error',
                            compact('kumihanCopyCode', 'redirectUrl', 'popupMessageType'),
                        );
                    }
                }

                $card->update([
                    'redirect_ecsite_complete' => config('card.redirect_ecsite_complete'),
                    'edit_count_complete' => $card->style['edit_count'],
                ]);

                if (session()->has("card_session_$hashid")) {
                    $this->service->saveSessionIntoDB($card->hash_id);
                }

                $urlParams = sprintf(
                    config('ecsite.redirect_urls.temporary_cart'),
                    $card->cart_id,
                    $card->cart_token,
                    $productCode,
                    $card->kumihan_id,
                );
                break;

            case 'mydesign_return':
                $urlParams = sprintf(
                    config('ecsite.redirect_urls.mydesign_return'),
                    $card->cart_id,
                    $card->cart_token,
                    $productCode,
                    $card->kumihan_id,
                );
                break;

            case 'customer_return':
                $urlParams = sprintf(
                    config('ecsite.redirect_urls.customer_return'),
                    $card->cart_id,
                    $card->cart_token,
                    $productCode,
                    $card->kumihan_id,
                );
                break;

            case 'mypage':
                $urlParams = config('ecsite.redirect_urls.mypage');
                break;

            case 'product_detail':
                $card = $this->service->findCardByHashId($hashid);
                $agency = $this->agencyService->findAgencyByAgencyId($card->agency_id);
                $agencyEName = $agency->agency_ename;
                $fullBackUrl = $card->back_url;
                $parsedUrl = parse_url($fullBackUrl);
                $path = $parsedUrl['path'] ?? '';
                $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
                $urlParams = $path . $query;

                break;
            case 'cart_return':
                if ($card->kumihan_id_temp) {
                    // Call api copy kumihan
                    $kumihanCopyCode = $this->kumihanService->copyKumihan($card, $functionMode = 0, $copyMode = 1);
                    if ($kumihanCopyCode !== 'OK') {
                        $popupMessageType = config('kumihan.kumihan_copy.popup_message_type.preview');
                        return view(
                            'errors.kumihan-copy-error',
                            compact('kumihanCopyCode', 'redirectUrl', 'popupMessageType'),
                        );
                    }
                }

                $cardInfo = session()->get('card_info');
                $isInternal = $cardInfo['is_internal'] ?? null;
                if ($isInternal && $card->kumihan_id_temp && $card->atena_kumihan_id_temp) {
                    // Call api copy kumihan
                    $kumihanCopyCode = $this->kumihanService->copyKumihan($card, $functionMode = 1, $copyMode = 1);
                    if ($kumihanCopyCode !== 'OK') {
                        $popupMessageType = config('kumihan.kumihan_copy.popup_message_type.preview');
                        return view(
                            'errors.kumihan-copy-error',
                            compact('kumihanCopyCode', 'redirectUrl', 'popupMessageType'),
                        );
                    }
                }

                //                $this->service->copyCardFilesToMountedStorage($card);
                $card->update([
                    'redirect_ecsite_complete' => config('card.redirect_ecsite_complete'),
                    'edit_count_complete' => $card->style['edit_count'],
                ]);

                if (session()->has("card_session_$hashid")) {
                    $this->service->saveSessionIntoDB($card->hash_id);
                    $cardSession = session()->get("card_session_$hashid");
                    $cardSession['redirect_ecsite_complete'] = $card->redirect_ecsite_complete;
                    session(["card_session_$hashid" => $cardSession]);
                }
                $urlParams = sprintf(config('ecsite.redirect_cart_page'), $hashid);

                break;

            case 'cart_return_from_atena':
                if ($card->atena_kumihan_id_temp) {
                    // Call api copy kumihan
                    $kumihanCopyCode = $this->kumihanService->copyKumihan($card, $functionMode = 1, $copyMode = 1);
                    if ($kumihanCopyCode !== 'OK') {
                        $popupMessageType = config('kumihan.kumihan_copy.popup_message_type.preview');
                        return view(
                            'errors.kumihan-copy-error',
                            compact('kumihanCopyCode', 'redirectUrl', 'popupMessageType'),
                        );
                    }
                }

                $card->update([
                    'redirect_ecsite_complete' => config('card.redirect_ecsite_complete'),
                ]);

                $urlParams = sprintf(
                    config('ecsite.ecsite_to_editsite'),
                    $card->cart_id,
                    $card->cart_token,
                    $productCode,
                    $card->kumihan_id,
                );
                break;

            case 'customer_return_from_sender':
                //                $this->service->copyCardFilesToMountedStorage($card);

                $card->update([
                    'redirect_ecsite_complete' => config('card.redirect_ecsite_complete'),
                    'edit_count_complete' => $card->style['edit_count'],
                ]);
                if ($card->kumihan_id_temp) {
                    // Call api copy kumihan
                    $kumihanCopyCode = $this->kumihanService->copyKumihan($card, $functionMode = 0, $copyMode = 1);
                    if ($kumihanCopyCode !== 'OK') {
                        $popupMessageType = config('kumihan.kumihan_copy.popup_message_type.preview');
                        return view(
                            'errors.kumihan-copy-error',
                            compact('kumihanCopyCode', 'redirectUrl', 'popupMessageType'),
                        );
                    }
                }
                if (session()->has("card_session_$hashid")) {
                    $this->service->saveSessionIntoDB($card->hash_id);
                    $cardSession = session()->get("card_session_$hashid");
                    $cardSession['redirect_ecsite_complete'] = $card->redirect_ecsite_complete;
                    session(["card_session_$hashid" => $cardSession]);
                }

                $urlParams = sprintf(
                    config('ecsite.redirect_urls.customer_return'),
                    $card->cart_id,
                    $card->cart_token,
                    $productCode,
                    $card->kumihan_id,
                );
                break;

            default:
                abort(404);
                break;
        }

        $url = $card->back_host . $urlParams;
        Log::info('Redirect away to ECSite URL.', [
            'kumihan_id' => $card->kumihan_id,
            'redirect_page' => $redirectPage,
            'url' => $url,
            'card' => $card,
        ]);

        return redirect()->away($url);
    }

    /**
     * @param Request $request
     * @param $productCode
     * @param $productId
     * @param $materialId
     * @param $agencyId
     * @param $areaId
     * @return Application|Factory|View
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function redirectToEditSite(Request $request, $productCode, $productId, $materialId, $agencyId, $areaId)
    {
        if ($request->has('ec_ss_id')) {
            session(['ec_ss_id' => $request->input('ec_ss_id', null)]);
        }

        $areaAgency = $this->areaAgencyService->getAreaAgencyByAreaIdAndAgencyId($areaId, $agencyId);
        $agency = $this->agencyService->findAgencyByAgencyId($agencyId);
        $vDesignM = $this->vDesignMService->findByDesignNo($productCode);
        $edit_redirect = '0';

        if (!$agency) {
            Log::error('Agency not found.');

            abort(404);
        }

        // get DM info from redis
        $dataDm = Redis::get('_cache:' . $request->input('session_id_dm', null));
        $dataDmRaw = unserialize(unserialize($dataDm));

        if ($vDesignM->kumi_ty === 'Y') {
            $phone_error = [];
            $phone_count = 0;
            $i = 0;

            if (!empty($dataDmRaw['dm_info']['edit_redirect'])) {
                $edit_redirect = $dataDmRaw['dm_info']['edit_redirect'];
            } elseif (!empty($dataDmRaw['dm_info']['addresses'])) {
                foreach ($dataDmRaw['dm_info']['addresses'] as $address) {
                    if (!empty($address) && !empty($address['phone_fax'])) {
                        $y = 0;
                        foreach ($address['phone_fax'] as $phone) {
                            if (!empty($phone)) {
                                $phone_count++;

                                if (is_null($phone['key'])) {
                                    $phone_error[] = ['ai' => $i, 'pi' => $y];
                                }
                            }

                            $y++;
                        }
                    }

                    $i++;
                }

                if (count($phone_error) == 2 && $phone_count == 2) {
                    foreach ($phone_error as $phone_index) {
                        $dataDmRaw['dm_info']['addresses'][$phone_index['ai']]['phone_fax'][$phone_index['pi']]['key'] = 'TEL';
                    }

                    $edit_redirect = '1';
                    $dataDmRaw['dm_info']['edit_redirect'] = $edit_redirect;
                }
            }
        }

        if (isset($dataDmRaw['dm_info'])) {
            session(['dm_info' => $dataDmRaw['dm_info']]);
        }

        if (isset($dataDmRaw['dm_info_new'])) {
            session(['dm_info_new' => $dataDmRaw['dm_info_new']]);
        }

        $user = auth_user($agencyId);
        $agencyEname = $agency->agency_ename;

        if (!isset($request->is_with_dm)) {
            session()->forget('dm_info');
        }

        $senderInfo = session()->get('dm_info') ?? [];
        $userLoginType = config('ecsite.login_types.not_logged_in');

        if ($user) {
            $userLoginType = config('ecsite.login_types.is_logged_in');
        }

        $backUrl = url()->previous();
        if ($request->has('back_url')) {
            $backUrl = urldecode($request->input('back_url'));
        }
        $backHost = get_back_host($backUrl);
        $userId = session()->get("$agencyId.user.id", null);

        if (app()->environment('staging', 'production') && $backHost == config('app.url')) {
            Log::notice('Not request from ECSite', [
                'productCode' => $productCode,
                'productId' => $productId,
            ]);

            // abort(404);
        }

        $cardStyle = [
            'typeno' => $vDesignM->dtype,
            'text_ty' => $vDesignM->kumi_ty,
            'allow_decoration_stamp' => $vDesignM->allow_decoration_stamp,
            'designcolor_name' => $vDesignM->designcolor_name,
            'designcolor_cd' => $vDesignM->designcolor_cd,
            'photo_flg' => $vDesignM->photo_flg,
            'printedmount' => $vDesignM->printedmount,
            'text_no' => $vDesignM->kumi_txt_no,
            'kumi_txt_no' => $vDesignM->kumi_txt_no,
            'pldptmng_no_str' => $vDesignM->pldptmng_no_str,
            'design_ty' => $vDesignM->design_ty,
            'edit_mode' => 0,
            'edit_count' => 0,
            'kumihan_count' => 0,
            'shotai' => $vDesignM->kumi_font_cd,
            'font_color' => filterFontColor($vDesignM),
            'han_color_k' => $vDesignM->han_color_k,
            'outline_char_kbn' => $vDesignM->outline_char_kbn,
            'isTypeSetting' => false,
            'photo_finishing_flg' => $vDesignM->photo_finishing_flg,
            'yoridori_kbn' => $vDesignM->yoridori_kbn,
            'item_kbn' => $vDesignM->item_kbn,
            'free_edit_flg' => $vDesignM->free_edit_flg ?? 1,
            'kumi_atena_faceprt_flg' => $vDesignM->kumi_atena_faceprt_flg,
            'dm_data_edit_flg' => null,
            'allow_corporate_use' => $vDesignM->allow_corporate_use,
        ];

        $isMochuu = is_mochuu($vDesignM->item_kbn);
        $isKanChuu = is_kanchuu($vDesignM->item_kbn);

        if ($vDesignM->design_ty == Card::IS_VERTICAL_DESIGN) {
            $cardStyle['width'] = 100;
            $cardStyle['height'] = 148;
            $cardStyle['align'] = 'vertical';
            $cardStyle['card_width_in_px'] = 2362;
            $cardStyle['card_height_in_px'] = 3495;
            $screenCard = config('common.storage_server.free_design.default_screen_card_vertical');
            $printCard = config('common.storage_server.free_design.default_print_card_vertical');
        } else {
            $cardStyle['width'] = 148;
            $cardStyle['height'] = 100;
            $cardStyle['align'] = 'horizontal';
            $cardStyle['card_width_in_px'] = 3495;
            $cardStyle['card_height_in_px'] = 2362;
            $screenCard = config('common.storage_server.free_design.default_screen_card_horizontal');
            $printCard = config('common.storage_server.free_design.default_print_card_horizontal');
        }

        if ($vDesignM->is_free_design) {
            $screenCardExt = File::extension($screenCard);
            $defaultScreenName = sprintf(config('card.default_name.screen'), $screenCardExt);

            $card = $this->service->create([
                'material_id' => $materialId,
                'area_id' => $areaId,
                'agency_id' => $agencyId,
                'image' => $defaultScreenName,
                'print_background' => null,
                'cart_id' => null,
                'product_code' => $productCode,
                'product_id' => $productId,
                'shop_key' => '',
                'add_order_flg' => 1,
                'kumihan_id' => null,
                'style' => $cardStyle,
                'elements' => $elements ?? [],
                'back_url' => $backUrl,
                'back_host' => $backHost,
                'reset_flag' => config('card.no_reset_flag'),
                'user_id' => $user->id ?? null,
                'user_login_type' => $userLoginType,
                'detail_mode' => config('common.detail_mode.group'),
                'dm_info' => $senderInfo,
            ]);

            $card->newYearCard()->create();
            $cardFolder = $this->service->getCardFolder($card);

            if (!File::isDirectory($cardFolder)) {
                File::makeDirectory($cardFolder, 0777, true, true);
            }

            if ($this->service->moveCardImage($cardFolder, $materialId, $productCode, $defaultScreenName)) {
                Log::error('Can not download card image.');

                abort(404);
            }

            Log::info('Created new free design card', [
                'kumihan_id' => $card->kumihan_id,
            ]);

            session([
                "common_card_session_{$card->hash_id}" => [
                    'design_id' => $productCode,
                    'product_id' => $productId,
                    'material_id' => $materialId,
                    'agency_id' => $agencyId,
                    'site_id' => $areaId,
                ],
            ]);
            if ($request->has('simple_flow')) {
                session([
                    "simple_flow_{$card->hash_id}" => [
                        'simple_flow' => 1,
                        'r001Url' => $request->input('simple_flow', null),
                    ],
                ]);
            }

            return view('cards.ecsite_to_editsite', compact(
                'card',
                'isMochuu',
                'agencyEname',
                'areaId',
                'isKanChuu',
                'areaAgency',
                'agencyId',
                'edit_redirect',
            ));
        }

        $masks = [];
        if ($vDesignM->photo_flg == Card::HAS_PHOTO_FLG) {
            $xmlExtendPath = sprintf(config('common.storage_server.master.has_photo.xml_path'), $productCode);
            if (!Storage::disk(config('common.storage_server.master.driver'))->exists($xmlExtendPath)) {
                Log::notice('xmlExtendPath not found.', [
                    'xmlExtendPath' => $xmlExtendPath,
                ]);
                abort(404);
            }

            $elementClips = extractClipFromXml($xmlExtendPath);
            $screenCard = sprintf(config('common.storage_server.master.has_photo.screen_path'), $productCode);
            $printCard = sprintf(config('common.storage_server.master.has_photo.print_path'), $productCode);
            $elements = [];
            foreach ($elementClips as $index => $clip) {
                $maskUrl = sprintf(
                    config('common.storage_server.master.has_photo.mask_path'),
                    $productCode,
                    $clip['mask_id'],
                );
                $maskExt = File::extension($maskUrl);
                $maskName = sprintf(
                    config('common.storage_server.master.has_photo.mask_path'),
                    $productCode,
                    $clip['mask_id'],
                );

                $masks[] = compact('maskUrl', 'maskExt', 'maskName');

                $x1 = $clip['dimensions']['x1'] * 600 / 72 / $cardStyle['card_width_in_px'] * $cardStyle['width'];
                $y1 = $clip['dimensions']['y1'] * 600 / 72 / $cardStyle['card_height_in_px'] * $cardStyle['height'];
                $x2 = ($clip['dimensions']['x2'] * 600 / 72
                    / $cardStyle['card_width_in_px'] * $cardStyle['width']) <= $cardStyle['width']
                    ? ($clip['dimensions']['x2'] * 600 / 72
                        / $cardStyle['card_width_in_px'] * $cardStyle['width']) : $cardStyle['width'];
                $y2 = ($clip['dimensions']['y2'] * 600 / 72
                    / $cardStyle['card_height_in_px'] * $cardStyle['height']) <= $cardStyle['height']
                    ? ($clip['dimensions']['y2'] * 600 / 72
                        / $cardStyle['card_height_in_px'] * $cardStyle['height']) : $cardStyle['height'];
                $elements[] = [
                    'width' => $x2 - $x1,
                    'height' => $y2 - $y1,
                    'x_coordinate' => $x1,
                    'y_coordinate' => $y1,
                    'style' => [
                        'type' => config('card.element_type.clip'),
                        'width' => $x2 - $x1,
                        'height' => $y2 - $y1,
                        'x_coordinate' => $x1,
                        'y_coordinate' => $y1,
                        'clip_type' => $clip['type'],
                        'original_coordinate' => $clip['coordinates'],
                        'mask' => [
                            'name' => $maskName,
                            'id' => $clip['mask_id'],
                        ],
                    ],
                ];
            }
            $cardStyle['total_clip'] = count($elements);
        } else {
            $screenCardName = find_design_screen_image($productCode);
            $screenCard = config('common.storage_server.master.has_not_photo.screen_path') . $screenCardName;
            $printCardName = find_design_print_image($productCode);
            $printCard = config('common.storage_server.master.has_not_photo.print_path') . $printCardName;
            if (!$screenCardName) {
                Log::notice('images not found.', [
                    'screenCardName' => $screenCardName,
                    'producCode' => $productCode,
                    'screenCard' => $screenCard,
                ]);
                abort(404);
            }
            if (!$printCardName) {
                Log::notice('images not found.', [
                    'printCardName' => $printCardName,
                    'producCode' => $productCode,
                    'printCard' => $printCard,
                ]);
                abort(404);
            }
        }

        // $screenCardExt = File::extension($screenCard);
        // $printCardExt = File::extension($printCard);

        // $defaultScreenName = sprintf(config('card.default_name.screen'), $screenCardExt);
        // $defaultPrintName = sprintf(config('card.default_name.print'), $printCardExt);
        $defaultScreenName = $screenCard;
        $defaultPrintName = $printCard;
        $cardInput = [
            'material_id' => $materialId,
            'area_id' => $areaId,
            'agency_id' => $agencyId,
            'image' => $defaultScreenName,
            'print_background' => $defaultPrintName,
            'cart_id' => null,
            'product_code' => $productCode,
            'product_id' => $productId,
            'shop_key' => '',
            'add_order_flg' => 1,
            'kumihan_id' => null,
            'style' => $cardStyle,
            'elements' => $elements ?? [],
            'back_url' => $backUrl,
            'back_host' => $backHost,
            'reset_flag' => config('card.no_reset_flag'),
            'user_id' => $user->id ?? null,
            'user_login_type' => $userLoginType,
            'detail_mode' => config('common.detail_mode.group'),
            'dm_info' => $senderInfo,
        ];

        $card = $this->service->create($cardInput);

        if (!is_mochuu($cardStyle['item_kbn'])) {
            $card->newYearCard()->create();
        }

        $cardFolder = $this->service->getCardFolder($card);
        if (!File::isDirectory($cardFolder)) {
            File::makeDirectory($cardFolder, 0777, true, true);
        }

        // if (!File::copy($screenCard, $cardFolder . $defaultScreenName)
        //     || !File::copy($printCard, $cardFolder . $defaultPrintName)) {
        //     Log::notice('could not copy images from /mnt/editsite.', [
        //         'defaultScreenName' => $cardFolder . $defaultScreenName,
        //         'defaultPrintName' => $cardFolder . $defaultPrintName,
        //     ]);
        //     abort(404);
        // }

        // foreach ($masks as $mask) {
        //     if (!File::copy($mask['maskUrl'], $cardFolder . $mask['maskName'])) {
        //         Log::notice('could not copy images from /mnt/editsite.', [
        //             'defaultMaskName' => $cardFolder . $mask['maskName'],
        //         ]);
        //         abort(404);
        //     }
        // }

        Log::info('Created new card', [
            'kumihan_id' => $card->kumihan_id,
        ]);

        session([
            "common_card_session_{$card->hash_id}" => [
                'design_id' => $productCode,
                'product_id' => $productId,
                'material_id' => $materialId,
                'agency_id' => $agencyId,
                'site_id' => $areaId,
            ],
        ]);
        if (isset($request->is_with_dm_new)) {
            session()->put("is_with_dm_new_$card->hash_id", 1);
            $dmInfoNew = session()->get("dm_info_new");
            if (!empty($dmInfoNew) && $dmInfoNew['numSenderInfo'] == 1) {
                $card->update(['dm_info' => $dmInfoNew['senderInfo']]);
                $senderInfo = $card->dm_info;
            }
        }
        if ($request->has('simple_flow')) {
            session([
                "simple_flow_{$card->hash_id}" => [
                    'simple_flow' => 1,
                    'r001Url' => $request->input('simple_flow', null),
                ],
            ]);
        }

        return view('cards.ecsite_to_editsite', compact(
            'card',
            'isMochuu',
            'agencyEname',
            'areaId',
            'senderInfo',
            'isKanChuu',
            'areaAgency',
            'agencyId',
            'edit_redirect',
        ));
    }

    /**
     * @param $hashId
     *
     * @return RedirectResponse
     */
    public function redirectToCart(Request $request, $hashId)
    {
        $exchange = $this->service->getSessionExchange($hashId);
        $card = $this->service->findCardByHashId($hashId);

        if ($request->has('is_sp_app') && $request->has('is_sp_app')) {
            session(["sp_app_back_url" => route('redirect.atena.order', [
                'typesettingId' => $card->kumihan_id,
                'is_internal' => 1,
                'screen' => 'edit',
            ])]);
        }

        $redirectUrl = back()->getTargetUrl();
        $kumihanCopyCode = 'OK';
        if (request()->input('only_atena', false)) {
            $result = $this->redirectCartFromAtena($card, $kumihanCopyCode);
        } else {
            $result = $this->redirectCartFromNaire($card, $kumihanCopyCode)
                && $this->redirectCartFromAtena($card, $kumihanCopyCode);
        }

        if (!$result) {
            $popupMessageType = config('kumihan.kumihan_copy.popup_message_type.preview');
            return view(
                'errors.kumihan-copy-error',
                compact('kumihanCopyCode', 'redirectUrl', 'popupMessageType'),
            );
        }

        if (session()->has("is_dm_fail_$card->hash_id") || session()->has("error_continue_$card->hash_id")) {
            $dataAfterPreview = session()->get("data_after_preview_$card->hash_id");
            $dataAfterPreview['edit_mode'] = $card->style['edit_mode'];
            $data = $this->kumihanService->create($dataAfterPreview, $card, true);
            if ($data['status'] == 'success') {
                if (!session()->has("after_preview_done_$card->hash_id")) {
                    $cardStyleEditCount = $card->style['edit_count'] ?? 0;
                    $card['style->edit_count'] = $cardStyleEditCount + 1;
                    $card->save();
                }
                session(["after_preview_done_$card->hash_id" => true]);
            }
            Log::info('Call Kumihan after preview.', [
                'data' => $data,
            ]);
        }
        $url = $this->service->redirectToCart($hashId);
        $card = $card->refresh();
        if ($card->edit_count_complete != $card->style['edit_count']) {
            $card->update([
                'edit_count_complete' => $card->style['edit_count'],
            ]);
        }
        if (session()->has("no_update_$hashId")) {
            if (
                session()->get("no_update_$hashId")
                && (isset($card->style['allow_corporate_use']) && $card->style['allow_corporate_use'])
            ) {
                $card['style->dm_data_edit_flg'] = 1;
            } else {
                $card['style->dm_data_edit_flg'] = 2;
            }
        }
        $card->save();

        $this->service->cardCart($card, $exchange);
        $sessionId = session()->getId();
        $kumihanId = $card->kumihan_id;
        session()->forget("card_session_$hashId");
        session(["cart_session_$hashId" => true]);

        return view('cards.editsite_to_update_cart', compact(
            'url',
            'card',
            'sessionId',
            'kumihanId',
        ));
    }

    private function redirectCartFromNaire($card, &$kumihanCopyCode)
    {
        if ($card->kumihan_id_temp) {
            // Call api copy kumihan
            $kumihanCopyCode = $this->kumihanService->copyKumihan($card, $functionMode = 0, $copyMode = 1);
            if ($kumihanCopyCode !== 'OK') {
                return false;
            }
        }
        $cardInfo = session()->get('card_info');
        $isInternal = $cardInfo['is_internal'] ?? null;
        if ($isInternal && $card->kumihan_id_temp && $card->atena_kumihan_id_temp) {
            // Call api copy kumihan
            $kumihanCopyCode = $this->kumihanService->copyKumihan($card, $functionMode = 1, $copyMode = 1);
            if ($kumihanCopyCode !== 'OK') {
                return false;
            }
        }

        $card->update([
            'redirect_ecsite_complete' => config('card.redirect_ecsite_complete'),
            'edit_count_complete' => $card->style['edit_count'],
        ]);

        if (session()->has("card_session_{$card->hash_id}")) {
            $this->service->saveSessionIntoDB($card->hash_id);
            $cardSession = session()->get("card_session_{$card->hash_id}");
            $cardSession['redirect_ecsite_complete'] = $card->redirect_ecsite_complete;
            session(["card_session_{$card->hash_id}" => $cardSession]);
        }
        $this->service->copyCardFilesToMountedStorage($card);

        Log::info('Redirect cart from Naire.', [
            'card' => $card,
        ]);

        return true;
    }

    private function redirectCartFromAtena($card, &$kumihanCopyCode)
    {
        $cardInfo = session()->get('card_info');
        $isInternal = $cardInfo['is_internal'] ?? null;
        if ($isInternal && $card->kumihan_id_temp && $card->atena_kumihan_id_temp) {
            $kumihanCopyCode = $this->kumihanService->copyKumihan($card, $functionMode = 1, $copyMode = 1);
            if ($kumihanCopyCode !== 'OK') {
                return false;
            }
        }
        $card->update([
            'redirect_ecsite_complete' => config('card.redirect_ecsite_complete'),
        ]);

        return true;
    }

    public function putSessionFormExchange(Request $request, $hashId)
    {
        $data = $request->all([
            'kousei_flg',
            'kousei_email',
            'oemail',
            'no_deal_reason',
            'accept_preview',
        ]);

        session()->put("exchange_$hashId", $data);

        return redirect()->back()->with('flag-exchange', true);
    }

    public function reorderCard($agencyEname, $areaId, Request $request)
    {
        $user = auth_user($agencyEname);
        $kumihanId = $request->get('kumihan_id');
        $card = $this->service->findCardByKumihanId($kumihanId);
        if ($card->user_id != $user->id) {
            return abort(Response::HTTP_NOT_FOUND);
        }

        $newCard = $this->service->reorderCard($card);
        $kumihanCopyCode = $this->kumihanService->copyKumihanForNewCard(
            $card->kumihan_id,
            $newCard->kumihan_id,
            $isAtena = 0,
        );
        if ($kumihanCopyCode !== 'OK') {
            return view('errors.kumihan-copy-error', [
                'kumihanCopyCode' => $kumihanCopyCode,
                'redirectUrl' => back()->getTargetUrl(),
                'popupMessageType' => config('kumihan.kumihan_copy.popup_message_type.preview'),
            ]);
        }
        if ($newCard->atena_kumihan_id && $newCard->atena_kumihan_count_complete) {
            $kumihanCopyCode = $this->kumihanService->copyKumihanForNewCard(
                $card->atena_kumihan_id,
                $newCard->atena_kumihan_id,
                $isAtena = 1,
            );
            if ($kumihanCopyCode !== 'OK') {
                return view('errors.kumihan-copy-error', [
                    'kumihanCopyCode' => $kumihanCopyCode,
                    'redirectUrl' => back()->getTargetUrl(),
                    'popupMessageType' => config('kumihan.kumihan_copy.popup_message_type.preview'),
                ]);
            }
        }

        $this->service->cardCart($newCard, $this->service->getSessionExchange($newCard->hash_id));

        return view('cards.editsite_to_update_cart', [
            'url' => $this->service->redirectToCart($newCard->hash_id),
            'card' => $newCard,
            'sessionId' => session()->getId(),
            'kumihanId' => $newCard->kumihan_id,
        ]);
    }

    public function redirectAtena(Request $request, $hashId)
    {
        $card = $this->service->findCardByHashId($hashId);
        $fromSpApp = false;
        $sendKey = $request->query('send_token');
        $receiverKey = $request->query('receive_token');
        $areaAgency = $this->areaAgencyService->getAreaAgencyByAreaIdAndAgencyId($card->area_id, $card->agency_id);
        $backUrl = env('ECSITE_URL');
        if ($sendKey !== null || $receiverKey !== null) {
            $fromSpApp = true;
            $backUrl = substr($backUrl, -1, 1) == '/' ? $backUrl : $backUrl . '/';
            $backUrl = $backUrl . $areaAgency->agency_ename . '/' . $areaAgency->area_id . '/';
            Log::info("Check send key and receive key", [
                'card_id' => $card->id,
                'from_sp_app' => $fromSpApp,
                'send_token' => $sendKey,
                'receive_token' => $receiverKey,
            ]);
            if (!$card) {
                return abort(Response::HTTP_NOT_FOUND);
            }

            if (!session()->has("card_session_$hashId") && !$card->redirect_ecsite_complete) {
                session(["card_session_$hashId" => $card->toArray()]);
            }

            if ($card->sp_app_send_key !== $sendKey || $card->sp_app_get_key !== $receiverKey) {
                if ($card && $card->kumihan_id) {
                    $hashId = $card->hash_id;
                    session()->forget("cart_session_$hashId");
                    $backUrl = $backUrl . 'api/cart/clear-by-kumihan/' . $card->kumihan_id;
                }
                return $this->redirectAtenaScreen($card, $fromSpApp, $backUrl, "認証情報が一致しません。SP APP error");
            }

            $expireMinutes = config('sp_app.expire_token');
            $createdAt = Carbon::parse($card->sp_app_created_at);
            $now = Carbon::now();
            if ((int) $now->diffInMinutes($createdAt, true) > $expireMinutes) {
                if ($card && $card->kumihan_id) {
                    $hashId = $card->hash_id;
                    session()->forget("cart_session_$hashId");
                    $backUrl = $backUrl . 'api/cart/clear-by-kumihan/' . $card->kumihan_id;
                }
                return $this->redirectAtenaScreen($card, $fromSpApp, $backUrl, "appトークンの期限が切れました。<br style='display:inline;'>再度年賀状アプリケーションから操作をお願いします。");
            }

            if ($card->redirect_ecsite_complete) {
                return $this->redirectAtenaScreen($card, $fromSpApp, $backUrl, "セッションタイムアウトになりました。<br style='display:inline;'>再度操作してください。");
            }

            if (!empty(optional($card->orderHistory)->typesetting_id) || !empty($this->mall3OrderItemRepository->checkCardOrdered($card->kumihan_id))) {
                return $this->redirectAtenaScreen($card, $fromSpApp, $backUrl, "The card has been ordered");
            }
        }

        $taxInfo = $this->getTaxInfo($areaAgency);
        $originalKumihanId = $card->kumihan_id;
        $updateOrCreateSender = $this->customerDetailService->exists($card->id);
        $checkCardSupportAtena = $this->service->checkSupportAtena([
            'design_id' => $card->product_code,
            'material_id' => $card->material_id,
            'agency_id' => $card->agency_id,
            'site_id' => $card->area_id,
        ], $hashId);
        $checkSupportAtena = $checkCardSupportAtena['has_atena'];
        // if (session()->has("is_dm_fail_$card->hash_id") || session()->has("error_continue_$card->hash_id")) {
        //     $dataAfterPreview = session()->get("data_after_preview_$card->hash_id");
        //     $dataAfterPreview['edit_mode'] = 1;
        //     $data = $this->kumihanService->create($dataAfterPreview, $card, true);
        //     if ($data['status'] == 'success') {
        //         if (!session()->has("after_preview_done_$card->hash_id")) {
        //             $cardStyleEditCount = $card->style['edit_count'] ?? 0;
        //             $card['style->edit_count'] = $cardStyleEditCount + 1;
        //             $card->save();
        //         }
        //         session(["after_preview_done_$card->hash_id" => true]);
        //     }
        //     Log::info('Call Kumihan after preview.', [
        //         'data' => $data,
        //     ]);
        // }

        return view('cards.redirect_atena', compact(
            'card',
            'originalKumihanId',
            'updateOrCreateSender',
            'checkSupportAtena',
            'taxInfo',
            'fromSpApp',
            'backUrl',
        ));
    }

    protected function redirectAtenaScreen($card, $fromSpApp, $backUrl, $errorMessage = null, $originalKumihanId = null, $updateOrCreateSender = null, $checkSupportAtena = null, $taxInfo = null)
    {
        return view('cards.redirect_atena', compact(
            'card',
            'originalKumihanId',
            'updateOrCreateSender',
            'checkSupportAtena',
            'taxInfo',
            'errorMessage',
            'fromSpApp',
            'backUrl',
        ));
    }

    public function getTaxInfo($areaAgency)
    {
        $taxInfo = app(TbTaxInfo::class)->select([
            'atena_base_price',
            'tax_rate',
            'atena_base_price_pretax',
            'atena_unit_price',
            'atena_unit_price_pretax',
        ])
            ->latest('start_time')
            ->first();

        if ($taxInfo) {
            switch ($areaAgency) {
                case $areaAgency->tax_calc_type !== 2 && $areaAgency->atena_price_type === 0:
                    return [
                        'basic_charge' => sprintf(
                            '%g',
                            $taxInfo->atena_base_price * ($taxInfo->tax_rate / 100) + $taxInfo->atena_base_price,
                        ),
                        'price' => sprintf('%g', $taxInfo->atena_unit_price * ($taxInfo->tax_rate / 100) + $taxInfo->atena_unit_price),
                    ];
                case $areaAgency->tax_calc_type !== 2 && $areaAgency->atena_price_type === 1:
                    return [
                        'basic_charge' => sprintf(
                            '%g',
                            $areaAgency->atena_base_price * ($taxInfo->tax_rate / 100) + $areaAgency->atena_base_price,
                        ),
                        'price' => sprintf('%g', $areaAgency->atena_unit_price * ($taxInfo->tax_rate / 100) + $areaAgency->atena_unit_price),
                    ];
                case $areaAgency->tax_calc_type === 2 && $areaAgency->atena_price_type === 0:
                    return [
                        'basic_charge' => sprintf('%g', $taxInfo->atena_base_price_pretax),
                        'price' => sprintf('%g', $taxInfo->atena_unit_price_pretax),
                    ];
                default:
                    return [
                        'basic_charge' => sprintf('%g', $areaAgency->atena_base_price_pretax),
                        'price' => sprintf('%g', $areaAgency->atena_unit_price_pretax),
                    ];
            }
        }
    }

    public function GA4(Request $request)
    {
        $query = [
            'design_id' => $request->design_id,
            'material_id' => $request->material_id,
            'is_form' => $request->is_form,
        ];
        if ($request->filled('_gl')) {
            $query['_gl'] = $request->_gl;
        }
        $url = rtrim(env('ECSITE_URL'), '/') . '/' . $request->agency_ename . '/' . $request->area_id . '/agree/?' . http_build_query($query);

        return redirect($url);
    }

    /** Get card preview image
     * @param string $kumihanId
     * @return image
     * Get image from s3 then return image content
     */
    public function getImagePreview($kumihanId)
    {
        $card = $this->service->findCardByKumihanId($kumihanId);
        if (!$card) {
            abort(404);
        }
        $imagePath = get_card_folder($card) . $card->image_preview;

        if (!Storage::disk(config('card.driver'))->exists($imagePath)) {
            Log::error('Image preview not found.', [
                'kumihan_id' => $kumihanId,
                'image_path' => $imagePath,
            ]);
            abort(404);
        }

        $image = Storage::disk(config('card.driver'))->get($imagePath);
        $mimeType = Storage::disk(config('card.driver'))->mimeType($imagePath);

        return response($image, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=600');
    }
}
