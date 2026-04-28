<?php

namespace Tests\Unit\Services;

use App\Contracts\Repositories\AgencyRepository;
use App\Contracts\Repositories\AreaAgencyRepository;
use App\Contracts\Repositories\CardRepository;
use App\Contracts\Repositories\DesignRepository;
use App\Contracts\Repositories\ImageRepository;
use App\Http\Requests\Api\Card\DuplicateTemporaryCardRequest;
use App\Services\DesignService;
use App\Services\VDesignMService;
use App\Models\Address;
use App\Models\Card;
use App\Models\Category;
use App\Models\Company;
use App\Models\Element;
use App\Models\Participant;
use App\Models\SampleText;
use App\Services\CardService;
use App\Services\KumihanService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Mockery as m;
use Exception;
use Imagick;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class CardServiceTest extends TestCase
{
    protected $model;
    protected $service;
    protected $mockHandler;
    protected $httpClient;
    protected $hashid;
    protected $kumihanId;
    protected $kumihanService;
    protected $imageRepository;
    protected $designService;
    protected $designRepository;
    protected $agencyRepository;
    protected $areaAgencyRepository;
    protected $cardRepository;
    protected $vDesignMService;

    public function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->model = m::mock(Card::class)->makePartial();
            $this->kumihanService = m::mock(KumihanService::class);
            $this->imageRepository = m::mock(ImageRepository::class);
            $this->designService = m::mock(DesignService::class);
            $this->designRepository = m::mock(DesignRepository::class);
            $this->agencyRepository = m::mock(AgencyRepository::class);
            $this->areaAgencyRepository = m::mock(AreaAgencyRepository::class);
            $this->cardRepository = m::mock(CardRepository::class);
            $this->vDesignMService = m::mock(VDesignMService::class);
            $this->mockHandler = new MockHandler();
            $this->httpClient = new Client([
                'handler' => $this->mockHandler,
            ]);
            $this->service = new CardService(
                $this->model,
                $this->httpClient,
                $this->kumihanService,
                $this->imageRepository,
                $this->designService,
                $this->designRepository,
                $this->agencyRepository,
                $this->areaAgencyRepository,
                $this->cardRepository,
                $this->vDesignMService,
            );
        });

        $this->hashid = 'rBmze1EPGdXQA98Ya98k3pq6glDjZRN0';
        $this->kumihanId = 'KI200602163933312027';

        parent::setUp();
    }

    public function test_create_failed()
    {
        $this->expectException(\Throwable::class);
        DB::shouldReceive('beginTransaction')
            ->andReturn(null);
        DB::shouldReceive('rollback')
            ->andReturn(null);
        $data = [
            'first_name' => 'asdasd',
            'last_name' => 'asdasd',
            'email_url' => [
                0 => [
                    "key" => "email",
                    "value" => "trasd@sdasdl.com"
                ],
                1 => [
                    "key" => "email",
                    "value" => null
                ],
            ],
            'addresses'=> [
                [
                    'postcode' => '1000001',
                    'prefecture' => 'asdasd',
                    'city' => '2131232',
                    'street' => 'adasdas',
                ],
            ],
            'participants' => [
                [
                    "last_name" => "rtret_7760_",
                    "first_name" => "ewrwer_7821_",
                    "furigana_old_last_name_age" => null,
                    "furigana_title" => null,
                ],
            ],
        ];

        $this->model->shouldReceive('create')
            ->andThrow(\Throwable::class);

        $result = $this->service->create($data);
        $this->throwException($result);
    }

    public function test_create_success()
    {
        DB::shouldReceive('beginTransaction')
            ->andReturn();

        $data = [
            'first_name' => 'asdasd',
            'last_name' => 'asdasd',
            'email_url' => [
                0 => [
                    "key" => "email",
                    "value" => "trasd@sdasdl.com"
                ],
                1 => [
                    "key" => "email",
                    "value" => null
                ],
            ],
            'addresses'=> [
                [
                    'postcode' => '1000001',
                    'prefecture' => 'asdasd',
                    'city' => '2131232',
                    'street' => 'adasdas',
                ],
            ],
            'participants' => [
                [
                    "last_name" => "rtret_7760_",
                    "first_name" => "ewrwer_7821_",
                    "furigana_old_last_name_age" => null,
                    "furigana_title" => null,
                ],
            ],
            'elements' => [
                [
                    'card_id' => '201',
                    'image' => 'name_area.png',
                    'width' => '21.75',
                    'height' => '66.50',
                    'x_coordinate' => '10.00',
                    'y_coordinate' => '73.50',
                    'style' => '{"text": "", "type": 5, "frame": "name_area"}'
                ]
            ]
        ];

        $this->model
            ->shouldReceive('create')
            ->andReturn($this->model);

        $this->model
            ->shouldReceive('elements->createMany')
            ->andReturn($data['elements']);

        $this->model
            ->shouldReceive('save')
            ->andReturn($this->model);

        DB::shouldReceive('commit')
            ->andReturn();

        $result = $this->service->create($data);
        $this->assertInstanceOf(Card::class, $result);
    }

    public function test_update()
    {
        DB::shouldReceive('beginTransaction')
            ->andReturn(null);
        DB::shouldReceive('commit')
            ->andReturn(null);
        $card = m::mock(Card::class);
        $card->shouldReceive('getAttribute')
            ->with('style')
            ->andReturn([
                'width' => 148,
                'height' => 100,
                'align' => 'horizontal',
                'kumihan_count' => 0,
            ]);
        $company = m::mock(Company::class);
        $card->shouldReceive('update')
            ->andReturn(1);
        $card->shouldReceive('addresses->delete')
            ->andReturn(1);
        $card->shouldReceive('addresses->createMany')
            ->withAnyArgs()
            ->andReturn(collect([new Address]));
        $card->shouldReceive('participants->delete')
            ->andReturn(1);
        $company->shouldReceive('delete')
            ->andReturn(1);
        $card->shouldReceive('getAttribute')
            ->with('company')
            ->andReturn($company);
        $data = [
            'first_name' => 'asdasd',
            'last_name' => 'asdasd',
            'addresses'=> [
                [
                    'postcode' => '1000001',
                    'prefecture' => 'asdasd',
                    'city' => '2131232',
                    'street' => 'adasdas',
                ],
            ],
            'email_url' => [
                0 => [
                    "key" => "email",
                    "value" => "trasd@sdasdl.com",
                ],
                1 => [
                    "key" => "email",
                    "value" => null,
                ],
            ],
            'style' => [
                'color' => '1',
                'kumihan_count' => 0,
            ],
            'user_photo' => 1
        ];

        $card->shouldReceive('elements->where->delete')
            ->andReturn(1);

        $card->shouldReceive('elements->createMany')
            ->andReturn($data['user_photo']);

        $result = $this->service->update($data, $card);
        $this->assertInstanceOf(Card::class, $result);
    }

    public function test_update_fail()
    {
        DB::shouldReceive('beginTransaction')
            ->andReturn(null);

        $this->model->shouldReceive('getAttribute')
            ->andThrow(Exception::class);

        $data = [
            'first_name' => 'asdasd',
            'last_name' => 'asdasd',
            'addresses'=> [
                [
                    'postcode' => '1000001',
                    'prefecture' => 'asdasd',
                    'city' => '2131232',
                    'street' => 'adasdas',
                ],
            ],
            'email_url' => [
                0 => [
                    "key" => "email",
                    "value" => "trasd@sdasdl.com",
                ],
                1 => [
                    "key" => "email",
                    "value" => null,
                ],
            ],
            'style' => [
                'color' => '1',
                'kumihan_count' => 0,
            ],
            'user_photo' => 1
        ];

        DB::shouldReceive('rollback')
            ->andReturn(null);

        $result = $this->service->update($data, $this->model);
        $this->assertFalse($result);
    }

    public function test_update_with_participants_and_companies()
    {
        DB::shouldReceive('beginTransaction')
            ->andReturn(null);
        DB::shouldReceive('commit')
            ->andReturn(null);
        $card = m::mock(Card::class);
        $card->shouldReceive('getAttribute')
            ->with('style')
            ->andReturn([
                'width' => 148,
                'height' => 100,
                'align' => 'horizontal',
                'kumihan_count' => 0,
            ]);
        $company = m::mock(Company::class);
        $card->shouldReceive('update')
            ->andReturn(1);
        $card->shouldReceive('addresses->delete')
            ->andReturn(1);
        $card->shouldReceive('addresses->createMany')
            ->withAnyArgs()
            ->andReturn(collect([new Address]));
        $card->shouldReceive('participants->delete')
            ->andReturn(1);
        $card->shouldReceive('participants->createMany')
            ->withAnyArgs()
            ->andReturn(collect([new Participant]));

        $company->shouldReceive('update')
            ->withAnyArgs()
            ->andReturn(1);

        $card->shouldReceive('getAttribute')
            ->with('company')
            ->andReturn($company);
        $data = [
            'first_name' => 'asdasd',
            'last_name' => 'asdasd',
            'addresses'=> [
                [
                    'postcode' => '1000001',
                    'prefecture' => 'asdasd',
                    'city' => '2131232',
                    'street' => 'adasdas',
                ],
            ],
            'email_url' => [
                0 => [
                    "key" => "email",
                    "value" => "trasd@sdasdl.com",
                ],
                1 => [
                    "key" => "email",
                    "value" => null,
                ],
            ],
            'company' => [
                "id" => null,
                "name" => "ashdasjdjas",
                "department" => "asdugasdasgjdas",
                "url" => null,
                'small_text' => 'test'
            ],
            'participants' => [
                [
                    "last_name" => "rtret_7760_",
                    "first_name" => "ewrwer_7821_",
                    "furigana_old_last_name_age" => null,
                    "furigana_title" => null
                ],
            ],
            'style' => [
                'color' => '1',
                'kumihan_count' => 0,
            ],
        ];

        $result = $this->service->update($data, $card);
        $this->assertInstanceOf(Card::class, $result);
    }

    public function test_update_with_participants_and_companies_small_text_null()
    {
        DB::shouldReceive('beginTransaction')
            ->andReturn(null);
        DB::shouldReceive('commit')
            ->andReturn(null);
        $card = m::mock(Card::class);
        $card->shouldReceive('getAttribute')
            ->with('style')
            ->andReturn([
                'width' => 148,
                'height' => 100,
                'align' => 'horizontal',
                'kumihan_count' => 0,
            ]);
        $company = m::mock(Company::class);
        $card->shouldReceive('update')
            ->andReturn(1);
        $card->shouldReceive('addresses->delete')
            ->andReturn(1);
        $card->shouldReceive('addresses->createMany')
            ->withAnyArgs()
            ->andReturn(collect([new Address]));
        $card->shouldReceive('participants->delete')
            ->andReturn(1);
        $card->shouldReceive('participants->createMany')
            ->withAnyArgs()
            ->andReturn(collect([new Participant]));

        $company->shouldReceive('update')
            ->withAnyArgs()
            ->andReturn(1);

        $card->shouldReceive('getAttribute')
            ->with('company')
            ->andReturn($company);
        $data = [
            'first_name' => 'asdasd',
            'last_name' => 'asdasd',
            'addresses'=> [
                [
                    'postcode' => '1000001',
                    'prefecture' => 'asdasd',
                    'city' => '2131232',
                    'street' => 'adasdas',
                ],
            ],
            'email_url' => [
                0 => [
                    "key" => "email",
                    "value" => "trasd@sdasdl.com",
                ],
                1 => [
                    "key" => "email",
                    "value" => null,
                ],
            ],
            'company' => [
                "id" => null,
                "name" => "ashdasjdjas",
                "department" => "asdugasdasgjdas",
                "url" => null,
            ],
            'participants' => [
                [
                    "last_name" => "rtret_7760_",
                    "first_name" => "ewrwer_7821_",
                    "furigana_old_last_name_age" => null,
                    "furigana_title" => null
                ],
            ],
            'style' => [
                'color' => '1',
                'kumihan_count' => 0,
            ],
        ];

        $result = $this->service->update($data, $card);
        $this->assertInstanceOf(Card::class, $result);
    }

    public function test_find_card_by_hash_id()
    {
        $this->model
            ->shouldReceive('findOrFail')
            ->andReturn($this->model);

        $response = $this->service->findCardByHashId($this->hashid);
        $this->assertInstanceOf(Card::class, $response);
    }

    public function test_find_card_by_kumihan_id()
    {
        $this->model
            ->shouldReceive('where->firstOrFail')
            ->andReturn($this->model);

        $response = $this->service->findCardByKumihanId($this->kumihanId);
        $this->assertInstanceOf(Card::class, $response);
    }

    public function test_get_sample_texts()
    {
        $category = m::mock(Category::class)->makePartial();
        $category->shouldReceive('all')
            ->andReturn(collect([]));

        $this->app->instance(Category::class, $category);

        $samplateText = m::mock(SampleText::class)->makePartial();

        $samplateText->shouldReceive('where->where->get')->andReturn($samplateText);

        $response = $this->service->getSampleTexts();
        $this->assertNotEmpty($response);
    }

    public function test_get_sample_texts_mourning()
    {
        $category = m::mock(Category::class)->makePartial();
        $category->shouldReceive('all')
            ->andReturn(collect([]));
        $this->app->instance(Category::class, $category);
        $category->id = 1;

        $category->shouldReceive('all')->andReturn($category);

        $category->shouldReceive('count')->andReturn($category);

        $samplateText = m::mock(SampleText::class)->makePartial();

        $samplateText->shouldReceive('where->where->get')->andReturn($samplateText);

        $response = $this->service->getSampleTextsMourning();
        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('categories', $response);
        $this->assertArrayHasKey('sampleTexts', $response);
    }

    public function test_get_image_parameters_from_ec_cube()
    {
        $response = $this->service->getImageParametersFromECCube();

        $this->assertJson($response);
    }

    public function test_upload_crops_success()
    {
        $input = [
            'crops' => [
                0 => [
                    'style' => [
                        'type' => 8,
                        'crop_id' => 21,
                        'uploadImageData' => [
                            'source' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAF8AAABgCAIAAAD0AjnaAAAAA3NCSVQICAjb4U/gAAAAGXRFWHRTb2Z0d2FyZQBnbm9tZS1zY3JlZW5zaG907wO/PgAADO1JREFUeJztXetTG1eW/51WS7IwQrKQEMIEgUECApg4OJiNTUw8E5KMk8pUamemams+zj+yf8Z82v0yUzPZ2uxUYieOk3jwOPj9wmBsIxKQTXgK8RAg9LpnP6gNMqBGwt0ilehXlKF1T98+96f7OOfcc9s0OTnJzO4Lf0UR2yArv4OBfVXjJwppvxX4SaPIjhqK7KihyI4aiuyoociOGorsqKHIjhqK7KihyI4a5N1FdFdBhtNDdgesdpgtMBoBIJFALIrIIi+GEZpCMrk/qu3LUwHAaCJvA7x+clVCMgBALMrRNUomALDlIFmqYLYQAJHiuWkERzg4ikS8kDruBzsHLNR8jHytkI1YCvOj+zwzgfAc4jEA/FyKAZjMcLjIXU3Vdeh8m14/xYEhfnQP69HCaFpYdojI30ZHT0CWORjA4wEOz6nJx2OYnuDpCR64Tg4XmtqpqZ0aWvjBDR4ZBLPavVqggOyUHJROvguXhyeDfOcKIkt53c3hOVz9hgdvUUc3dXRTTYPo/wprqzopm0ah1iynW3r/D7CXi/6L3HcuX2o2EVnivnOi/yLs5dL7f4DTramWW1EQdjyvSGc+QjwmLnyiTZgtGBAXPkE8Jp35CJ5XNKgwC/Rnx+mWut9HZFFc/HTvXWY7Ikvi4qeILErd7+vXg3Rmp6RUOn0W0VVx6XPEtF5oYlFx6XNEV6XTZ1FSqnHlAPRlh0g62QvJIPrOaU9NGrGo6DsHySCd7AWR5tXruGaRvw0uj+i/uGVA0SEnm8x7rzeZwPzs5mVkSdzsk072kr+NnzzYe7U7QTd2Dljo6AmeDG6dhu3l9N7v6aW+Z+YL//OCoRQMcF0jHT3BwYC2hqJe7FDzMcgy37mytcBkBhFC0/z8+ydnBcorEQzwehQANbRAIh4ZAkCWEtQ0YH6aQ8+FyyvgrGTZuKVWvnOFPvgPaj7G965q2Ap92DGayNfKwUC2RYrDs5vEHX+Lyt0iMITZSQBU34wU0qVcUSXVNPD83KZwRzc5K3eoMbLEwQD5Wnnotoa+mC6zMnkbIBvxeECPyrPi8QBkI3kbNKxSnzXL68dSeBcfSmtweA5LYXj9GtapAzuyTK5KnhjLVV6kwIyoBh4TT4yRqxKyZtOFDuw4PZAMPDORozjf7Ref/vfmDJWxnBERmJHz+sYzE5AMcHry0FYV2s/KZHcAQF7DKsNW5JFBiJTyd2gaY485OJprPeG5tAI8/SyPp2eHDmuW1Y5YNB3KygqDjNKyHUs4MARgo5SHbmde7jJq4jHEorDa81M4O3Rgx2zh6Jq6CNU1Uu2eps/dzEiOrsFs2UvNO0EHdoxGSiZ2CdutRnh5YQ91k9WOskNqAskEG7fainvG/kTdeeop395mRueCjm5SZUdb6LBmJRLbLf2M0jhEiuzOPbnURA4XRAqJRDYJlo0qpflCh74Ti5KlKuvIWpzHyjJcHunDP/LYYx4bwUoOIbGSUqprpCNNKLUhsoDFUDZBspTw3NTeFN8OHdiJLMJsgcm887LFLC59Jp14G+5qauuk1uOYn+VggMdHEFvfKmyQyduAWj9VHIYkgQVmJsT1S1l3I0xmmC2ILGrVFO3Z4cUwAXC4MJ3FIFxbEf/8XOkOtX6UV5Czko69iamnHHjIk0EAcLjI10JeHwxGgLG2yj884rEnWFlWe7bDlVZAq7bo0HdCUxApcldzNnbSWFvhh3f44R065EStn+oa4fHS4Tr++5/BLL33OzCQSvAPwzwewMyPuTyZ3NUQKYR+yiMrmeS5aaqu44HruYjzQggLIb5/Tfr3PwEGpJIAIARA4n//S7nMDVRdx3PTGm666+OjB0dgc5DDlY8iEmQZqyvK5doKCBn7xruDHC7YHAiO5PHQXZXSsK4NcHAUyQSa2vO4x1IKwRxRTEReXoDg/HYamtqRTOThlOUAffpOIs6BIfL6YLXleAcdLIUkYSWiXK9EIEmUOztWG3l9HBjSNklDrx0bfnQPySR1dOd6w0EriBB/vqjH10GUzVPdDuroRjLJj+7lr6kadNvPWo/ygxtU5YXXl5P8AQsAiBR1naGuMxACQK7+pNdHVV5+cEPzzBUd/SweGaSaBqmzR4Rnd98jlo0AyFMDlweAsoTn4k9abVJnD+ameGTwZTXeBj33QplF/0WIlNTzwe69IG1YC4H1KNajnLaGt1vPW2C2SD0fQKRE/1d6pPPovI++tiIun4floHTmQ3WCeOwJ4utwV/NCiOdnyV2N6CqPPVGr3GyRznwIy0Fx+bxOiTz652CEZsSVL2G1S70fqy1hsXXRdw7LC+Q+TJ5XsLIkrn6j1nesNqn3Y1jt4sqXCM3ooTgAUs72/fk/dXqAAqdbOn0WkkHc7FNL4ZEk2MoBYGlemZh3hNcndfZApMTl8/pRg8JFv0Iz4stPpJO90slermvMmhknBBZUw/VWG3V0U5UXc1Oi/yLWVtSEXxqF6jvK0/LMqsy8NZ1V6fUhmfw5ZlUCYOYnDzgYUDJyaxtpKcwTY5kZuS8gMyPX5kAywY8HCpmRW9i+kwn1bG7ZSJYSZZn7ZWVzp5GI8+gwRof5xZMAyo7C2irPTf2CTwJsIJnE9DOtdi+1RfGMjRqK7KihyI4aiuyoociOGvaJHVmG5eD+PDofFHxFP2ChQy5UeKihhfu/hkScTFL1EWX7gYgHbsBoojd/rTgKP47DaoNs3PQbJInvX9ckk25XFLzvlNqo7Tg1tEAIeq2LTp+F1UZHGpFKwmgifxuIAMbT7zk4yk9HeTGM9Shi64jH0q4G1fohFUjtgrMTmhY3L0MyiLvf8cO7SCURj0MIhGawpOzwkq+VjndLnaelN05TyzFejQBI9x0eL+h7ggo9sqiiik7/hr8fJtmIxnZ+PIBEjMOzaHgVQNpi5ieDWF5QhtLaKlntMBjADKLcMyw1QaHZ4bkpvnZJajrKDhfi66ioooqqdBEBov9rJOIwmcl/VOksP47zUpiUICEVzDtPo+CzMjNmJtD9Lo2PcGZTTWYcaVJSnlhweE6Zp9ciVFFFFVUcmiZ/Wx5p0Fpg3+wdJkLmzwtljNWI8hOLAeDZyec5C7pHvDKxfz662aKEdRRFMjUhanhV2bR5OgqAav1UWZ0uKpyG+8MOEQAqsyOV2vwwvUin/2VGaRl/8w+qa4TJDCF4fgaBhyCCKGigp+DsGGRY7Tx8l7w+LC2IW30A6M13qKSUH92nklKOx9Kskb8VNgdmJ3noNsBIJKjWTzYHgAJElNMoODuWEqnnLC8v8sggjw4jHSf91xeofxU19eRr4W/+oWTxLC+CsWEEAqA6P8oO8dQzrO+SLa4V9iOunD4bkm/RfmA/1iyV9v+UqMHPNoJxQJujEvsZdaf2E+SpEV//X16pk7tXe/wtiBSSCX5w8yWr0uF81nu/g0EmSXpu6UkwGETfuRcOkQMwmamhBdE18rUqkkYjD99TdqxkI3Wd4ccDCE1nfVB7FxzOreYhgWST+NcX9Pqpl2+LDn1nMggQswAzhKD6JphM2zNIqKMbsoxUkmqOgBlmC6w2LM4raZE2B7k8VF3HD27wcEa+W7kbK8vKabdUEsnklqmKbA6U2en1Uzx89+WbokOue2Z/ttqo7Q0efbglWEX1zVTr47v9G28foBM9ZDJvulHzM+KLv9GpXmrvwvLixud0tJMOWMSXn2DjYNuLoHc+xtoKX/tWE59D31mZXj+FZIIfvvA1UlM7dfbw0+9hPkAne2EpgaWEanz8w6MXrOfYOved5/vXeGJc+aS6jiqrlaMCO6LsEJxuDo5q5Y7p+R6M1g6qquF7V7cab0T8dJSvfUs19VT/Kr33e14KA9jhLRapFD+6r/x9yCl1ncHq8hauM6ul491IJXn0oVZN0O1ND691UfMxpFLU0oGV5czIw0aDeTzAM5PS2x+S+zAPXEf285Lk8tCpXhCJ775Om9c7yLQeJ/dhvn9tl5Mm+UCHkWWQ6cTb1HyMx56Iz/+CyCKdepeaX9tRlsorUFrG0882+8gWlB2irl/Rr38LkOg7j/DsDjIGA3X2UOtxnnrGmr5AQdO+Q0Q19dTWCauNnwzw3asAxLef0aleeu3fYDLzwI1NYaOJ2k+Qr5VnfuQrF7ZayRVVVOsnpxs2B4Tg0WEevLWDe2WQqa6RGo+izM7jI3zjn9pa21qPrCPNYMGXPuONM0OpJH/3FXo+gKcGQ3fShh/VN1N7F0xmHr7Lg7e2pwiS0URHmjg0g4Eb/HQ022AhbwO98Raiq3zzMn8/rHFbtPdCzQeQiO+QEGk0wWDYjAqXllF7Fw/d3tiH2ArJAIMhl1wm8vp4Ykxba3sDWvedbDm0iTgyJ9OVZe6/qFaPSG280UAdrOf/9fAz9UI1QpEdNRTZUUORHTUU2VFDkR01PF/RczyB9wvD/wNIi77O7U97wgAAAABJRU5ErkJggg=='
                        ]
                    ],
                    'image' => 'data:image/png;base64,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'
                ]
            ]
        ];

        $this->model
            ->shouldReceive('elements->where->delete')
            ->andReturn(true);

        File::shouldReceive('isDirectory')->andReturn(false);

        File::shouldReceive('makeDirectory')->andReturn(true);

        $cardFolder = public_path('uploads/cards/0/');
        if (!File::isDirectory($cardFolder)) {
            File::makeDirectory($cardFolder, 0777, true, true);
        }

        File::shouldReceive('copy')->andReturn(
            storage_path('app/tests/preview.jpg'), sprintf('%spreview.jpg', $cardFolder)
        );

        $jpgPath = public_path() . config('path.upload.card') . 0 . '/' . 'preview.jpg';
        Image::shouldReceive('read->core->native->getImageColorspace')->andReturn(13);
        Image::shouldReceive('read->save')->andReturnSelf();

        $this->model
            ->shouldReceive('elements->createMany')
            ->andReturn($this->model);

        $response = $this->service->uploadCrops($input, $this->model);

        $expected = [
            'status' => 'OK',
            'message' => 'success',
        ];

        $this->assertEquals($response, $expected);
    }

    public function test_upload_crops_with_cmyk_image()
    {
        $input = [
            'crops' => [
                0 => [
                    'style' => [
                        'type' => 8,
                        'crop_id' => 21,
                        'uploadImageData' => [
                            'source' => 'data:image/png;base64,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'
                        ]
                    ],
                    'image' => 'data:image/png;base64,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'
                ]
            ]
        ];

        $this->model
            ->shouldReceive('elements->where->delete')
            ->andReturn(true);

        File::shouldReceive('isDirectory')->andReturn(false);

        File::shouldReceive('makeDirectory')->andReturn(true);

        $cardFolder = public_path('uploads/cards/0/');
        if (!File::isDirectory($cardFolder)) {
            File::makeDirectory($cardFolder, 0777, true, true);
        }

        File::shouldReceive('copy')->andReturn(
            storage_path('app/tests/preview.jpg'), sprintf('%spreview.jpg', $cardFolder)
        );

        $jpgPath = public_path() . config('path.upload.card') . 0 . '/' . 'preview.jpg';
        Image::shouldReceive('read->core->native->getImageColorspace')->andReturn(Imagick::COLORSPACE_CMYK);
        Image::shouldReceive('read->save')->andReturnSelf();

        $this->model
            ->shouldReceive('elements->createMany')
            ->andReturn($this->model);

        $response = $this->service->uploadCrops($input, $this->model);

        $expected = [
            'status' => 'NOT_RGB',
            'message' => 'CMYK',
        ];

        $this->assertEquals($response, $expected);
    }

    public function test_upload_crops_fail()
    {
        $this->model
            ->shouldReceive('getAttribute')
            ->andThrow(Exception::class);

        $response = $this->service->uploadCrops([], $this->model);
        $expected = [
            'status' => 'ERROR',
            'message' => 'something went wrong',
        ];

        $this->assertEquals($response, $expected);
    }

    public function test_upload_images_fail()
    {
        $input = [
            'exportImage' => ''
        ];

        $this->model
            ->shouldReceive('getAttribute')
            ->andThrow(Exception::class);

        $response = $this->service->uploadImages($input, $this->model);

        $expected = [
            'status' => 'ERROR',
            'message' => 'something went wrong',
        ];

        $this->assertEquals($response, $expected);
    }

    public function test_upload_images_success()
    {
        $this->model->hash_id = $this->hashid;
        $input = [
            'exportImage' => 'https://loremflickr.com/600/800?random=1',
            'isChangedPhoto' => 1,
            'images' => [
                0 => [
                    'src' => 'https://loremflickr.com/600/800?random=2'
                ]
            ]
        ];

        $addresses = m::mock(Address::class)->makePartial();
        $addresses->id = 1;

        $participants = m::mock(Participant::class)->makePartial();
        $participants->id = 1;

        $this->model->setRelation('addresses', $addresses);

        $this->model
            ->shouldReceive('load')
            ->andReturn($this->model);

        $response = $this->service->uploadImages($input, $this->model);

        $expected = [
            'status' => 'OK',
            'message' => 'success',
        ];

        $this->assertEquals($response, $expected);
    }

    public function test_upload_images_validate_url()
    {
        $this->model->hash_id = $this->hashid;
        $input = [
            'exportImage' => 'https://loremflickr.com/600/800?random=1',
            'isChangedPhoto' => 1,
            'images' => [
                0 => [
                    'src' => 'data:image/png;base64,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',
                    'style' => [
                        'mask_id' => 1
                    ],
                    'extension' => 'jpg'
                ]
            ]
        ];

        $addresses = m::mock(Address::class)->makePartial();
        $addresses->id = 1;

        $participants = m::mock(Participant::class)->makePartial();
        $participants->id = 1;

        $this->model->setRelation('addresses', $addresses);

        $this->model
            ->shouldReceive('load')
            ->andReturn($this->model);

        $response = $this->service->uploadImages($input, $this->model);
        $expected = [
            'status' => 'OK',
            'message' => 'success',
        ];

        $this->assertEquals($response, $expected);
    }

    public function test_getColorSpaceName()
    {
        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_GRAY),
            'GRAY',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_TRANSPARENT),
            'TRANSPARENT',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_OHTA),
            'OHTA',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_LAB),
            'LAB',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_XYZ),
            'XYZ',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_YCBCR),
            'YCBCR',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_YCC),
            'YCC',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_YIQ),
            'YIQ',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_YPBPR),
            'YPBPR',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_YUV),
            'YUV',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_CMYK),
            'CMYK',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_HSB),
            'HSB',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_HSL),
            'HSL',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_HWB),
            'HWB',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_REC601LUMA),
            'REC601LUMA',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_REC709LUMA),
            'REC709LUMA',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_LOG),
            'LOG',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_RGB),
            'RGB',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_SRGB),
            'SRGB',
        );

        $this->assertEquals(
            $this->service->getColorSpaceName(Imagick::COLORSPACE_UNDEFINED),
            'UNDEFINED',
        );
    }

    public function test_upload_images_validate_url_and_cmyk_source()
    {
        $this->model->hash_id = $this->hashid;
        $input = [
            'exportImage' => 'https://loremflickr.com/600/800?random=1',
            'isChangedPhoto' => 1,
            'images' => [
                0 => [
                    'src' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/7gAOQWRvYmUAZAAAAAAC/9sAQwAMCAkLCQgMCwoLDg0MDhIeFBIRERIlGxwWHiwnLi4rJysqMTdGOzE0QjQqKz1TPkJISk5PTi87VlxVTFtGTU5L/9sAQwENDg4SEBIkFBQkSzIrMktLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tL/8AAFAgA8AC0BAERAAIRAQMRAQQRAP/EABsAAAEFAQEAAAAAAAAAAAAAAAABAgMEBQYH/8QAPhAAAQMCBAQDBAoABQQDAAAAAQACAwQRBRIhMRNBUWEGInEUMoGRIzNCUmKhscHR4RUkQ3KCNFOS8CVz8f/EABkBAAMBAQEAAAAAAAAAAAAAAAABAgMEBf/EACgRAQEAAgIDAAIBBAIDAAAAAAABAhEDIRIxQQRRExQiMmFCUnGRof/aAA4EAQACEQMRBAAAPwDyoboD1VSfZKr40CjO6lF9hCCCDuinQtTBKbPJxSLuafJ2PM/Dl3KvDHZb0F3+DwNpaYzSchdzncuwTzvyLwmu6o1r87hCCczjYAfqVfo71bn1NxwneWO3PkT6b+qm3XRzvtn1WWACnZdz7ZpHHW3QLTMvDZZvJZ62vembDQNmmzPvr1VZ9SSd7K/FNya0OGMa02AKT2/Id0eJeZJMBhmALhYqSPEwTY6JeJzJUqPDDcv0bvgVajq2P5hHiryZVVgtRADZpcB1Cma4HUFTo9s2SN7SWuaQRyKUi6DRtcWEEC9uSYW23VEsNmDiC06cu3Zef+P8DZTvGJ07LNkdlmA2DuTviiVU7df4bxA1MJgkddzBdt97dPguUpdGf8T+Zsm0w6xbS0cacY8LwuNu7mvkPxdb9le+kXfxGzWSQ+g/L+1j3mPIo3EazSJeHK7dwHxT3B4ZBIGFgcSbkiyLTmOgvSM7qWCmgb/pwMB9bJz0tWpmNcJHkXzSE/t+y8taLkarKOSRZTyLXTrTQUZBUs7AhBBK7dOnQuq8K04c3zhuS13HnYarW/24px7przZvrotqeb/EKv2KB54LHWleDu7mB6DRGM1PKjK+V8YrRsLc0xHnIs0HkP8A3VdMx7GQsDGhrGizWjkFlrda71Gcyne6R2t3Oddzio3yX/8A1XIztXIoQLaWt2VeQk6XVSItW2AAbKrISnotpAonE33OiWj2VPhlcDoUSDyIQCLELQpq2Rp966LjtUzUK7Cqaqac8YB6hadPVtkFnaFZ3HTWZOWxTA5acl8JztHLmrfvNUNGJrFJfbqFnY3RitwmqgLcxdGco/EBcIVLqtbBqz2bEoJL2aXWd6HQryKIZGkHcBo/dVGsmo75XvEZLKmmh/7NJG34kXP6oyQjh2cerisnMUtmkTrmyYCkoozUVtPDvxJGt+ZT+J32a92Vpd0F13mI10cNZI11t9PQafstTR07bQRjnl1XmQ3WDjTKU7FW2CYXWUo3oIz9kbLyCHWLkfRlQumwuUUtK0PNnPda3Zb2bYw1wuLLa8MwOLM5aQ573l7hsTfl6AKcr8Xh+0UxytdrfawXRl4sABYDZTIrKoY4i3VxzHmmG5VyM7UoACjdomz2kbqoHDVBbPUcjEj2VMDSDdBbCsRW5qjmRHbKzHJY25I0uZK80Ie3utOjqMwDSsssW+GTlvEGFZC6VgAv06q45ZtWBC4gG+jmrybGqMU+PVVMwEB1RZo7G38pxtvrb0fDZ/aMOp5iQS6ME262VPxJIJMarLbNkyD0aLJZe0/E0ItE3uLrNUhIlvonsBa3hSES4/SE7R5pD8BdXPcT9RVX1DgPteX56KbHZ3PxB1js0fz+6uriQCwsuZCycRVKRZW1CjIupRYEiSQnH3gn9OhdPhkIq8SidIy8MUY8t/e3t+hW9jOd015sNF1tLJwYodAC2O1gOpWVna51EQiDwb398ndTMeXuVSItPLQBopzeypFpgtdRuuU0WpBZMy35ILZbpr2oGyhR27IBU5t/gmCJ7AUz2QqzE8tItyRZtpjkq1UDZo3Nds4WWxC7PCCubKarrxu44Kvi9nr5GEb6/NeeYh/mPGuVxzM9oDvQAA/snPba+o7jC2cHBYGc2xWXKVcvHqJZecj3P+ZWdJoAWAHRRXtokNlQ43TFC6LwdH9PXT/9unyD1cbLTH2UQz6uib1ff5arNr5eJWzO6vNlS0yxlm4gpeXwVtfgTHA7qU2X2EhHNJNn0JT7yf0/oXb+D4eNTzTOOoygDtYj91tb1GWM7qvVyZGAXtm5rTnnYHvYw6xuynW+ylVSxe4O+qvUfmAdb4qoytJKbN3Vz4ISh73TSLpop7T1KYRZBbPBumORBs4JjW3TBbp/DsEA3NqjQFBlT2apriOTQLYozan17rDP27OP04bxECcVFvwi3xXnDKjj43XVo2jglkH/AI2H6pRvfenb5BFTxRAW1a1ct07ALKhYSc0AJTvZOChdV4cb7PgNXUHTiTWv2a2/6rXEoiOtS0fdYT8z/S5txu4koWlVAbqHGFae3XRU2CY4fRlFF9BNYMwREzuBI8WeQl9K+wu38DTN4HCPvEut8NVr/wAWWtZ2KuItJp7jkRf02/dOxMiHFahjDzDj8QlOyyS0zi+BjjuQuowljXUkcjtA4XF0rUyfay8YqpA/hQgkjeyuudC3lconlRbjFGGHEpRcPyt7nVROdE77NlU3Gdsq5FDWx2PGzdiFFIwctVTOr9PI86PFiqsgshK0FJTtaRdxsmqIqlz2i0bcxKsAQnTMUu2kmKjI+vZ5mxx2HK51TvZI3C4dcI2vwnxVOMzxuDZYcru/NROh4ThbYpyp1qr9PWNq4idnDcLRp23piOrSsc/bq4/Tj8ZdfGCekgA+Fl5gBwcOxeT8DIR8XfwEr6bfXdSG8sI7l35f2uedufVZKTIagglG91UFC6x49k8JUzNjIxzz/wAnfwtJ6OIYtZpXdw35D+1h0tKZ4y4dbIktG0yyiFLGwK1INSmYUJOmXqkYSRNIOqIUmgmyfWu9UFfYXReDpuDijM3utY535K560zy/y2iqWcSFzDfzaaKSvqDUY9UAEi8gBsiIyp0LckTW9Au1pJf8uxo0AFlUjDLNFJTtMheRqU8lx5FNnbae1rG9Alj10N0F2R9hronOu3RMyNs43Cry7FNKUJsd3NsElA2GqlyG2iBslxzTRO6I72TVMjJaeOZtntDgeqtCYTRdxqiNfLcZzaI0tW0sH0bwQey0KY/QNPZY5+3Tx+o5bGoyMUlaBc5ibLznF4uDhtSwjKZ68j4NBP7pZem09u2pzmfF+GEH52/hcvwgdb/ksz0tJWxgi2bbsgQJWwF7rNdcnTZVINBdX4r/AMvSU9MNmNaz/wAW/wAlaX0aGl1izffcXfmqWGAR0bLjV1yrx9JqZcwdQVii+grcm49AnQFXPvBIwh5y6hBUK1hUDKnFoY5QCwv1BO/ZOTtzfkZ3DjuUC9BjEbaN7GxNa5twwW0utcpp534n5U5bqopmm7SNr6+i5ekh/wDnJmu0IcLpfXdlekq7qjiblbfZF6Ya3VWtmdHE4xi7raLTpxG5z25B5bbrO2uzDHHXTkcakrIooJH1bgZs18mgFuSjrnRRcMFti9+UEBLHK7Vlx45RN4edWS8U+0GQMjz5S640Ox+HNVZLXIP2TYraOKzTpad2eNj2+69ocFRmOuqZLjdlJS2NhzJskqTd0iqH8OMuOzRdawoo8gzPIPayj+R0zgx125CXxPW8dxhpmOY29g4kkgc9NlRno2ve9scge6M+YbEKsctss+Hx7jcocbc+GB9ZTuhbOLxvaczSeh5g+qrfVDKrZytawfY7ha8IvSRm5uBcAcysspvJ1TOYYbrja7Ocfqo2RtfxCIyT9kG11wHjJ3BFNA0EZBI833uSB/KM5rprxZ+ePk66naQ5zjbZo02XLD3QsWqdLH7t+uqZwLSwGn9pxKmjtcOlbf0Burg2jnfkhkd0aStDxhLxa5rR0LvmVWQhYW5ImN6NARE3JGxn3QAtZ6SeuTto70XOgK2/UMPVoRTChd7wQAh4uEChNDnMqMzSQ5rrgjkhFku5Qu+8M4l/iUJbKRxove79Ctd7jw+X8f8ApeS+Pq9wjhcELPxRvB8QyuaLXs4oj0PLymyM9xvot+jle8AlxFlTK5aKQDuLrSiq5YzpY+qmyHjy5YqNbhFLWNyyNI1v5TbXqnyTmUte8Nu33dNlMxiv5sqZR4VT0Ub44Q7LIbuud1EXXuSdzclWna42MMDWsADWiwA5BU5yHOtdKVNSjZPp38ORpH5p2KxysuzJ4hNGWG453BWv/iDWxWezN3Gqz8W8/I/ccxL4Te6qMtPVcNpJJbaxF+6yPaYoHyGJjs8jsznFXjizz5rk6GKksxjJHAsYLNYBp8b7qEyOkluStESrIAAsNlrTVTaSNjnnRkWg6kqJ1ujlmXJcePFj4ZTNlmrakgXlnIDrfZGi4Xxi8yVYzDUQxtPYm7issnpYYzHHxjSpdWvdyc91vhp+y522yhonTtggBdJ4OgvWmUj6qNzvja37rWD4r1msTWffe1v5qni59oxt7dw14b8ke6E42VgrVJVyY5+i5yCtH6qM/hCY+BQP3CQgRnFwCUC0Jn+qfUoTPYW74SqRS4v5nhrZG216hVi4/wA7DeHlPgVzFqpk+PwytGVjmA6lUy45Zx6IumpABEFcZWlVoGw3QQTg4uSOEsESXDCUl7IN1nOc4OPdGk3I5SZ9Lc0xsKVsxy2KNERVpnXN05C2VSUwBe2/xVLxpryQ0kb20T8ScaiZg5F2gWeV707fxpuXOo6eJtLTNYNmC5PU8yub8QxtqcWqQ4kMbJYWHQAfsos264ShFqSK+5bf56rOfRxB4DDmvzIIS8Yek6t02Auqr5HNa0aXIKLqHoLpcFw7/DY6oOcHEZWkgW7lVLsqpzyCSop2jYZnfsP1XKQ3mrpJTzJd8ynj7FXFcWiQuSZuucoFYYb08fa4QXwKOU2agfAq/NJmFIPrD6prnsKSHyVTc5cGFwzEDkj6jOWyyBbmIOpDXUjqOUSNa0A25G606+OLCZzGzOaC7GkIMDO4VMKE6S4KpOwn0+rh3U1cI7YqV7i8lttEC5EaALX3KqTRHkqRs5JHZzspGoU1UoSSAtNk4mhREZimWwp4hkbfmhpvUIuUrsRqX18ssVRI1oecga7QAKbHp8c8cJAQCCCLg7qu6onfIXvkLnONyTzKNNZQAGgACwGgCc2pLS0ujuAb6FLxVMircw7HqKniDJI5gc1zZoP7qLharyhkgcWnLa9tLq3UeIqCSiqGxvkEshcQHRkbiwVTGwtqsVNK2oY91srY2t0PPcrnKN8Tc5L2gnTXRPGaKrqtixFxqrILkWDVc5QKxFrTt7Ep/CnoKOWwbdIfAoWuGYeUI2nYSt9/4oOBSOJ47RfRIW9hSU5y1jOhKqM+SbgXpOHgGBh7LWPLyCsPbfZNmEzhlpBBsbqa1xoVyB1ibXaTvbmmEM8TXgZmtcAbi42TXZWkOaADfTRBHZc7Sx+oI1Varma6TPu87myKcLFGImBjRZo2CY8ZwCEROR6jZGb3TpQLOx6sNOYoGsLw5pLwHW0/9uk7/wAbGa3QsYSUb9wYz+IfuEOsKRtLDJ9U9rvRyZhNdQuDv5CYCidSvbuLoMJvBdexaQg4FaoKLjXdl8o1JOyDCmdVYfG4tyPktuWDRIBcgxYJgU0P/Tn/AHFEKegmTDyIK+goGgZhrzQnQSt974oOBTu+uZpySV9CTUSi3ROJvYXf+HqsVNIxwIuNHDoVpK8zlw8boLbaAbEqnP8ASFTCDNsLpLiF9Q1nvG3ql4LgbZSPVG4eyNqI3tu2Rrh2N1GYXe7Y6G+yNltJnbvca91XdSl5umvRTI0blSCANbqhFK14dsmWa0EuNgNbotVjjbSlYdW4VUr3uja4E+W/ILK16mGPjjoBVnUUDwfKWnlZPyaFULsKDheNzSfkq8gFEIK2AeUvy9CMwVbhhObWSjSSFru40R7AUgr6XKeM18enIXTUFWq659YwRQtdFTN+zfV/c/wgEUDYyBsmZVgMBuuZECnp/wDp3f7k56KBRze4UFfQVdoOYeqET2E+PU/FCoFYcBma4m1lKgmxgveSxjn2GpA2VSbRcpAtLBcRlw6R1UATTZgyRv3iencKow5ZM+voXoVFNFV07JYXh7HC4IVyvPyxspCbK0CdkxEZAOqrVfiKmo3cLNxpBvlOg9So8NtJxXLtXbRMc4uyhgPKySl8R0czssn0bjtm2+aPCi8OU9HSUpAGW5A6K0+YvcHX05W2VSaE6KynY1hAbYncndNncLXvYc06fjs+C7W2Kxa2qM3kj+r69Vlbt28XF4932mVZrR6JNypw26oMILUwE4At2d+aZhOcxrx9I0Hva6DChGH08lyWga33t+t1W6ZEpo2WtlaQP/e6NmVQuooyfq3fAj+UbAXFgWusWYUlIbwPH4lc9FAmTizCkL6CrNPmHqhGwntFicpNuZI2Qe9BaFNSRy0mdwLpM1rkq5JpnlbsKzTwuho5mtGU3cPzVSaiLf7gtXCsNhqcI4ThfLNmPxH9I10xztmWyfa+C6jDqFsEAEPlI5cijTP37RTy8O2lwsfxfWV1K+KBjuFBKwklu7jfUX+SbTiwx9iCRkzczTe29+S5cEjZDoTJzXm9ynKVC1MPxiejIAdmj5sdt/SvqsrhshF1o1uO0VZSvheydpcLhzSAWnkouKscbjdmZXg+Uiyw4sQrI25DLE+2z3MN/wAlHg380iczFa1gN+BI7k3KQPmlcKcyCs0WNcSZsVVFwnO0Dmm4v3UKlC2cqaglDBZBhLYoMJbG4umYTcgvcaHsmAix+85BhcCWaHzBZo0FJRQkxvfezS6wVSdFAm1eRjSCSXHYJChRR0+YNIumgK7BRDKL663Qm0LRpIQ3itA0BBWkZZBTzR+eYDYm/wAwqib7Ct+HpRFIYX6Nk09DySTnNkPXoutpxlFgkwQVFnDVVfEGGf4nh5Y0fTR+aPueY+KqKxy1VaCQQzfhdoV585jmEhwIINiCjTplaKAEGE8Rvc0uaxxaNyAnsrlN6CWGCSeQMibmcUrdDLKYzdC04PDtVI3NK+OIcrnMT8kplKyvPj8CSXAJQSIqiN56EFqPJU5oFn1WH1dLrNA4M+9uPmEXVbY5y+qF1GDFuKUwdHNEJG+VzCTmBU44y/U582WF14o5ZhFq5rrdQFpjCZbfWs+RVfxf7R/VX/qgOIwD73yTZMLqA0lmR7hsL2ul/H/tc/K/eIGI05Ni+3roq5patpAfSv15hzXfun/HVz8nD6lZUwv92Rp9CqbKymcQBM0ON7B3lJ5HdTccp7jeZ431UqnAvqEllXN0/hSvnPmyMHU3KjxRox8jGDzOATcdhpMGhhpIJhNUgHiNGtj1PT0VbK3RIpOLchpDeRPNYlLAah7pJNR+qlCRX4IbNLeuyZUK3TjQt6apooVqnH1h6uA+QVRFClLc7392qoQUDBYnkmAuowbEhUNEUzrTDa/2/wC0mOeP2K87SBpt+i2Wm6GSpI09FyviTDWx1Zny2hmOrh9l/wDauWXqtMbfi3Ry5mZCfM39FhGjfG+1iWnmlZpfnFhdLhtKz2aPLoQNR35rHLHeW3Blbc7sJ5pIoJDOIWB+2mlwqa95TWwrcMtNLp5mO6FEiZhYQkjYXTX0BcSWG/dM+4TO3mbHuqkjshLXjMDoQeaFynLnKyGTA8SiqqJxa06tHLu09lnd43p34WcmOqa9ge0tcLgrucJxODFafi07iC0gSMO7Tbb+1vMtxz5YXG6rFq4zTPtILgjykKxMJTLCGGwuS7TkmIjjkp+DOXi5AAAvrfsifOxr33udmADry7oipDaXhyvZG1paB5pHE9OfZVMSFHPHlqKeCoDW7PtmA7XTmzmKzSRVGcniysu6wNszb91jy0eHsLRBTPa3KCQHPFj8HKv/ACreX7aMRmyniOZe5tY8lyVT4pxSfQVHDB5MFrLk26vJMI2N2aPkspv0j8zi4kne17oI5acAGQAC1kqVCsRA7fJNNCuxRATsOzZAf0VfEBEJ+iv1JKCoU7Hec/7VUSE1zLWcOiewFHTl1XUtbA4iNmrpAOfQfypt7cv5HP8AxTr2FvQ4rU0r+HNaZoGhOjreqrY4rOTCZX2gkpmu90lv6K6cXpZo3MmjflcLFpaHAhC/D9IvZpWG4LTbvZZ8VFDLLMKYu4TSC0OOw6el1Gfl1qublmUzm/SzHmyDiWzdlbhLab3gWja4Vb/Za2crEsbahgcwg+iFToXtuqphy621QvZVNFUOgNjZ7DuOiY9mPZm52I2KKqCORnEhcHN59QhOrCRvc7R7Mrh3uCsjEaUV1I6E++3zMPQoym46OLLxqRN8EzUsFbNC6QtmkaGtDtASDqPVRjZt1cuNsZfiBkrqNro23ax13W3AXakLTbDTmxLdNcNrjbZB6TxONiAdDv3VGupDUuY4PALNmuFwfVVLpUjTw+rFOxzC0kO3IOoUkbHhjRI7M8DUjZLatFfI3OTG3K0nQFeR0sf2XM1WDdurQjYGANDbAJlQrDYg/YWKSNhTQRE+W3mCCoRU1sLY2wwh09S1x8sYuB6lVE2yd0JKbD8WqGizYqdv4hcp6rC8+M9BaMPh8uH+Yrpi882nKE9MbzZ30FJJgNKGZXvmeD1kNihH8uf7CmpcPbTi0UsrQNvNdJz54zLuhFdTSBrJWyB52IcP4SuUx+K4+a8c8ddBRW0FwQVTvwy8sdhKx74nh8ZIcELslmqFrRvGIU9y0Mew2e0deyTj1rK4mjy7m6ZHBJASWXtzHIo0ouhU8Tmy5mPdaTkHD8rpimklp2u3tyVeaNzDY3TOHA32ULhm0QZVCAWONtQqMLCx2H2avZNES0yAPuOTgd1jnNV3cN3j2Qi4sV13h3xFHiTG09QQysA+Encd+ycyLLDXccvi+EupXOmhGaAm5A3Z/S3HKpU6UYbqNyoaXInFMskaxn7LzERG9wNVm1dGrkEAm02d0QQU8dLIw2LSkQTK6VudtNTXMzhZ5afdHT1KuTbHky8YFq0FDHQQMa0AHme6txX+67oWnTsMgzNsG8ySkV6Nc4N3Nkjp8Pa8iXEKcO6cQH9EIsy/QzX2BSuqcNy6VIfb7rSf2Qz8c6W56KqKwPNoYRbq91klfwz7l/8AComlmyXdDHY7ea6m6+lOPj8tXKhVQS7dD0scZjNQJxLWjqULCsYbPw6ggmwk0Pqm5ubD/nCEXFitt7C5l279E2cu1cPySZXbdVnVEVvNc3TVpYBumNqnEZJGh/Q80DRMoBuNFG9gOrQmdOUbIjc3VCBZPimPKaV34XD8wsc/bs4PVIsEOcxzXscWuabgjcFZugEAggi4PJeh+HscZisHDls2qjb5x94feC0xqLi5nE8NdRSmSL6lx0/D2WqVSdIYnJqBpOHCy8pNZiD5C9zA97tSSy1/kp0nzkdKAALDYKVjcVk1b9H3a2yfjU3lhVXkhr55ckk0zyeryjxL+QLo8Bwv2ezyNeS01pzZXdC3Hw5moSFUdhIqDeZxLPu3SKkUzMGpItY4mNtsAEJtoSvp2Ae6FOiKlhibmFm6JiwK46lzQZSLWN1GU2zy0S4useYZHai1yUsbuOz8fO5Y9/CqEu7pujQTHygCwStPQWzhVcaiLK8+dv5jqql248+Pwv8Aox7QeSvPLXt8/wA1QhgBYfLt0WfNDZ3lNx2TGkoNwnxtFkzkCkDB0QqQEqljOHGvpmhhAlYbtvseoUZTbo48vGmg69lylRQzU9xKwsI6rHVdUsvo9VoqmalmbLA90cjDcOG6qGa9jZGFjwHNIsQV33hzHW4xC5kjMlTEPPYeV3cfwrlTYwcQoPZHZ2G8bjp1C2E06VQViMw6JpuGj5K9OF0+cKf2VmWwAT0ROImMw+JricovboggZAp2QCNgDRayBYQS3KjNmlCdJBqENc52jbJDQNhqnthkO7mj4pJpvEHQp7qcEauv8EtM7b8AeoyBH7puhMxyvunbpxkcGZjc22HVTbpWUmEId7BYFVIXVL8xub8uXZJ28Es45s5QuKTfQUD3hSegpWVTqXI9nvD80/RZYeU0F0sEglhZLGbseLrWOKT9mOtex3T8rHDoU1G5ntPUJnDDTomeih5PJKRqhcguo3OQZwChmjjqGlkzGvb0ISs2vG2Fss2fAqSQ6Zm+hU+EazOhaFC1lDE2OnYGNb05nqeqrWh5UyWFkrC2QZgeq1I62MtBeCHdktVczjImwmUSHguaWcrmxTbLRwtHMg6BBFGpSBwQA5psnE3SM0XBUTmg7oTYmaTZM0B0SLR+qkaSRskXiYdDuni50QPGGFwGu/ooKmenphmneB0bzPwSpzC30e0ucNNFk1uJvqGZYgY4z8ypbYcEl3l3UgFlnNFzok6NFRJ5QbqaAoQzPz8x1CJDCe4skFnNMbhyO3zSspwLQwvEo6MGF5L4yb+UXylPG2Iz4pbue0crC4At3C3WZZWB7CHNOxW0rG4WI+IWmztCkIsmnR4dfZMc8BCihpKhJuUwemoOFSEoUEg3QAngoMi0i1NhpnNlBG6Y4FNNixG9pG6ZlKQ0kzt6hKA62xQrVMc5gPvAepSEdQkLDmu6EJDbqR8Ek6OBJ5Ap8bHyGzGk90U5jagqqqCkZmnka3oNyfgnSRuY7K8KLW+PFPqOmrYqqLiQO+BGqy67DhIS5rvMddVLXUW4pc7drLHdG6Mlp5KhpKlhaTe6QCjm8z7JaOQIa1rvVpsmegpb2be+/MhM9BOjYHPzBt7b2Fgg5CLpaCLIxjXDlqp2dm2fiUh4TshIcNiFbqKMEZo/knjl+2WXH+mThmOuziKrA3sJB+6zHxEFasri6NkgcNEzLqgtH3SZbIPQukyoMXSEWTAQEj0F0Hs/VR5DwcMcbI9wEoMDR3T8qPGBmM1DzYENTRGwfZCW6XjErq+pe23GcPTRP0GyDVyHPOZxJPcprgDuAUDSWF0jD5HubbobJMkY+y35I3S1EpnqnbzS2/3FOBA2QaJ8Tnm7iSTzSPDXtsfgUhLo+lfLSy54/wDk3qs6sgIjNztzUtJ26XD6yOc+TQkbHksh1OHDMVRr6gmbw2gAWukRVWcy13HlqnpQTadoe8gG1+6VOQLRmp+GxrnTtkeLAb7Ina7joxpJPukBJTP49Qy4DYmaho2CL1B7OI0Ntyuggs4AhSGPiGZmbMbNGqulwA1OgQHNsjMj9AS5x0ACyMQxGlaSGAvf1bstMZRcJfbpcIpK2Fg4zw2Pkx2pH8KGGRkzczdD0O6rtjePTTOZu/zTiChGigppTGihRkpno5KALJHohK6UlZG8xa1McUEsRRk6phKZaWGNdfZNKY0sNGmiQlBJo2g7JpQErQi5QRwa22u36IQDSAT3SSNbIwtdsUjh0Ej4JRJHo5v5rLqKZ0TyN2HYpqnboaKsZVR3Gjx7zeipYg0xFkgGlkRSwx2a/ZZ7pmy5mkakbIUcoS0h+eO1uiRhTiRzowwNtvclUcCmjeGQuIPa6zz9LkC1MJrGspnSzGzWmw7qMTsZ+K0z6pgjitmO5PRVMQxOSqJAOWPk0fut8cRrSSgw6GiZ5RmkO7zv/SpMbc3ctZCtXFYjlyHfZPSSHVXI6xjhZ+h6qbiVxRmOx8u3ROJDhdpBHZSjxOCba5QNHKRrdEDRhdqtBuOYa42FdBf/AOwLHf8Ao/CuNPh3EG/6Tfg8KdtTFMLwyskH4XAomUHhkkiw2phFpYHDvuEF6pOkraW/JJnTGjzS27noEZ7oLQFO4JC6yBpI2Anff9UmdBaO4BPr+qXMmRvBsi6Ejh667pHAOaQ4XBRpRWZ4pA+M5XDms7EoSIS22ZvIpaaS7bGH1LZ738snNqw6im+0NE9K0uqq4StOmqXYgT2iV4s+zW87IXoJrpDI9sMWvILO91cgVjiWa2MHyt2W2OGoqktrdK0ElaSJKpT5QrSFDI+yCCgdKeqegErKlzToSEaMKZuIPG7ip8YNEWrS41BwQJfeGmixywu+lKk9EZJC5r7A8l55ZNzLicxzozmY4tPVpsUex6C18P8AE2I0ZAdL7RGPsS6/I7qLhPnS/O/e0MtLFLu2x6hdfhGM0uLMPCJZM0XdE46juOoU7s6p+Ms3ioz0z4dRbJ96y0LqkIMoB79Si6oi5Ul0EcWjcfFKCgjCzmnhBaQOFiUXsgFLS62lykNiCCLg8kHA1rmvDmnK5uxCy66jyNLmas/RDSXbXo6vjDLJYSD81lyFjO6Gi0qM8j5DlbcDoou6qBWm04o6exH00g83YdFphjpcNGpvy5JkTDq4rUU5WGtyi6NICikk1sN1Q0FE/ZA0FXebJgKtJKQgaCgMjjzRTCUPdbdSYWZcrn25QjMjZhKHAp7IJ8M0lPKyWF5ZIw3a4bgour0cuu4QgEWIuCvQsCxUYtRcQgNnYcsrR16+hWc/tuq0vc8oz6iDhPBHuHbstFXtmht00VetrYqOLNJfXZo3Kz5OWYNeLhy5bqJYIHSuuNANysp2Nvnv7OQ0DQ21K4OT8nk3109DD8PDH/LtbbSxjcZvVXKPFhbLVua08n7X9Vpw/ly9Zsef8O++P/0rVNAL5oR6tST47TsNo2vkPUCwK1y/Lxn+M2jD8DO/5XQiw8geZwH5qB2M1Lvq6ZgH4nFY382/pvPwMJ7yTChj5lxKYcYrR71LC4dA4on5t/R/0PH8tL7FHyLgRsbqhNUte8udTviB5A5gFpPy8b7gv4V+ZJ2ghoBNz1VnD3UtzJxGmUe6x2nxXTx8uGfqscvx+TDuwOBJtsOZSyNMkhJ1XVGZyGsu7bQIKhJMcoKZBV2t+0UwFDNJyCBoKvIdEz0FUcb3QAoyEEEJALOuuTbngRdPZhGhT0Ai9t0EFs+FK00uLxsJ+jn+jcP0/NRn63+l8fvX7MlZnjIXfXVbRVAt5Ku+Fk0x4jQ5trWK838m28j0eD+3j6XaYZYh3XO19PHTYg6OkOY28w+72K58u529HjtuO8kqkhoi8h0hJKy8v0Lnr0FeZSAW0CJKyuYUnAaAnovIKN0YOwSVKFE6nBQfkFVnomuGyqZWLmYUDZqijNjeWLodx6Fd3D+Xlj1l2z5ODDk7nVC0qaWOaDiRuvffqOy9THOZzcebyceXHdZBQuZxH2Ow3WiAo6h2UWCIAqmW13OTVoKtO7y6IAUXDs27tyhIUTggBNGyRhf/2Q==',
                    'style' => [
                        'mask_id' => 1
                    ],
                    'extension' => 'jpg'
                ]
            ]
        ];

        $addresses = m::mock(Address::class)->makePartial();
        $addresses->id = 1;

        $participants = m::mock(Participant::class)->makePartial();
        $participants->id = 1;

        $this->model->setRelation('addresses', $addresses);

        $this->model
            ->shouldReceive('load')
            ->andReturn($this->model);

        $response = $this->service->uploadImages($input, $this->model);
        $expected = [
            'status' => 'NOT_RGB',
            'message' => 'CMYK',
        ];

        $this->assertEquals($response, $expected);
    }

    public function test_export_preview_success_type_crop()
    {
        DB::shouldReceive('beginTransaction')->andReturn();

        $this->model->id = 21;
        $this->model->image = 'screen.png';
        $this->model->style = [
            'width' => 100,
            'photo_finishing_flg' => 2,
            'photo_flg' => 1,
            'height' => 100,
            'type' => config('card.element_type.crop'),
            'x_coordinate' => 100,
            'y_coordinate' => 100,
            'angle' => 1.5,
        ];

        $element = m::mock(Element::class)->makePartial();
        $element->id = 1;
        $element->image = 'screen.jpg';
        $element->style = [
            'mask_id' => 1,
            'type' => config('card.element_type.crop'),
            'x_coordinate' => 100,
            'y_coordinate' => 100,
            'angle' => 1.5,
        ];
        $element->width = 100;
        $element->height = 100;

        $data = [
            'style' => [
                'clipDbId' => 0,
                'mask' => [
                    'name' => 'screen'
                ],
                'scale' => 1,
                'angle' => 1.5,
                'crop' => [
                    'width' => 100,
                    'height' => 100,
                    'x_coord' => 10,
                    'y_coord' => 10
                ],
            ],
            'image' => 'screen.jpg'
        ];

        $clipElements = [
            0 => [
                'style' => [
                    'mask' => [
                        'name' => 'screen.jpg'
                    ],
                    'x_coordinate' => 100,
                    'y_coordinate' => 100
                ]
            ]
        ];

        $this->model
            ->shouldReceive('getClipElements->keyBy->toArray')
            ->andReturn($clipElements);

        $this->model
            ->shouldReceive('getImageElements')
            ->andReturn(collect([$data]));

        $cardFolder = public_path('uploads/cards/0/');
        if (!File::isDirectory($cardFolder)) {
            File::makeDirectory($cardFolder, 0777, true, true);
        }

        File::shouldReceive('copy')->andReturn(
            storage_path('app/tests/preview.jpg'), sprintf('%spreview.jpg', $cardFolder)
        );

        $jpgPath = public_path() . config('path.upload.card') . 0 . '/' . 'preview.jpg';

        Image::shouldReceive('read->core->native')->andReturn(new \Imagick(realpath($jpgPath)));

        $this->model
            ->shouldReceive('getMutiTypeElements')
            ->andReturn(collect([$element]));

        $this->model
            ->shouldReceive('pluck->toArray')
            ->andReturn(collect([$this->model]));

        $this->imageRepository->shouldReceive('getListStampImage')
            ->andReturn(
                collect([1 => 'images/icon-stamp-1'])
            );

        $this->model
            ->shouldReceive('save')
            ->andReturn(true);

        DB::shouldReceive('commit')->andReturn();
        DB::shouldReceive('rollback')->andReturn();
        Log::shouldReceive('error')->andReturn();

        $response = $this->service->exportPreview($this->model);
        $this->assertEquals(false, $response);
    }

    public function test_export_preview_success_not_type_crop()
    {
        DB::shouldReceive('beginTransaction')->andReturn();

        $this->model->id = 21;
        $this->model->image = 'screen.png';
        $this->model->style = [
            'width' => 100,
            'photo_finishing_flg' => 2,
            'photo_flg' => 1,
            'height' => 100,
            'type' => config('card.element_type.crop'),
            'x_coordinate' => 100,
            'y_coordinate' => 100,
            'angle' => 1.5,
        ];

        $element = m::mock(Element::class)->makePartial();
        $element->id = 1;
        $element->image = 'screen.jpg';
        $element->style = [
            'mask_id' => 1,
            'type' => config('card.element_type.image'),
            'x_coordinate' => 100,
            'y_coordinate' => 100,
            'angle' => 1.5,
            'imageId' => 1
        ];
        $element->width = 100;
        $element->height = 100;

        $data = [
            'style' => [
                'clipDbId' => 0,
                'mask' => [
                    'name' => 'screen'
                ],
                'scale' => 1,
                'angle' => 1.5,
                'crop' => [
                    'width' => 100,
                    'height' => 100,
                    'x_coord' => 10,
                    'y_coord' => 10
                ],
            ],
            'image' => 'screen.jpg'
        ];

        $clipElements = [
            0 => [
                'style' => [
                    'mask' => [
                        'name' => 'screen.jpg'
                    ],
                    'x_coordinate' => 100,
                    'y_coordinate' => 100
                ]
            ]
        ];

        $this->model
            ->shouldReceive('getClipElements->keyBy->toArray')
            ->andReturn($clipElements);

        $this->model
            ->shouldReceive('getImageElements')
            ->andReturn(collect([$data]));

        $cardFolder = public_path('uploads/cards/0/');
        if (!File::isDirectory($cardFolder)) {
            File::makeDirectory($cardFolder, 0777, true, true);
        }

        File::shouldReceive('copy')->andReturn(
            storage_path('app/tests/preview.jpg'), sprintf('%spreview.jpg', $cardFolder)
        );

        $jpgPath = public_path() . config('path.upload.card') . 0 . '/' . 'preview.jpg';

        Image::shouldReceive('read->core->native')->andReturn(new \Imagick(realpath($jpgPath)));

        $this->model
            ->shouldReceive('getMutiTypeElements')
            ->andReturn(collect([$element]));

        $this->model
            ->shouldReceive('pluck->toArray')
            ->andReturn(collect([$this->model]));

        $this->imageRepository->shouldReceive('getListStampImage')
            ->andReturn(
                collect([1 => 'images/icon-stamp-1'])
            );

        $this->model
            ->shouldReceive('save')
            ->andReturn(true);

        DB::shouldReceive('commit')->andReturn();
        DB::shouldReceive('rollback')->andReturn();
        Log::shouldReceive('error')->andReturn();

        $response = $this->service->exportPreview($this->model);
        $this->assertEquals(false, $response);
    }

    public function test_export_preview_fail()
    {
        DB::shouldReceive('beginTransaction')->andReturn();

        $this->model
            ->shouldReceive('getAttribute')
            ->andThrow(Exception::class);

        DB::shouldReceive('rollback')->andReturn();

        $response = $this->service->exportPreview($this->model);
        $this->assertFalse($response);
    }

    public function test_find_cards_to_create_pdf()
    {
        $this->model
            ->shouldReceive('where->get')
            ->andReturn($this->model);

        $response = $this->service->findCardsToCreatePDF();
        $this->assertInstanceOf(Card::class, $response);
    }

    public function test_find_card_to_make_print_pdf()
    {
        $this->model
            ->shouldReceive('where->get')
            ->andReturn($this->model);

        $response = $this->service->findCardToMakePrintPDF();
        $this->assertInstanceOf(Card::class, $response);
    }

    public function test_update_card_creating_pdf()
    {
        $this->model->id = 1;

        DB::shouldReceive('table->whereIn->update')->andReturn($this->model);

        $response = $this->service->updateCardCreatingPdf($this->model->id);
        $this->assertNull($response);
    }

    public function test_update_card_not_created_pdf()
    {
        $this->model->id = 1;

        DB::shouldReceive('table->where->update')->andReturn($this->model);

        $response = $this->service->updateCardNotCreatedPdf($this->model->id);
        $this->assertNull($response);
    }

    public function test_update_card_completed()
    {
        $this->model->id = 1;

        DB::shouldReceive('table->where->update')->andReturn($this->model);

        $response = $this->service->updateCardCompleted($this->model->id);
        $this->assertNull($response);
    }

    public function test_update_complete_order()
    {
        DB::shouldReceive('table->whereIn->update')->andReturn($this->model);

        $response = $this->service->updateCompleteOrder($this->kumihanId);
        $this->assertInstanceOf(Card::class, $response);
    }

    public function test_get_dpi()
    {
        $pixel = 1024;
        $milimet = 100;

        $result = 260;

        $response = $this->service->getDpi($pixel, $milimet);
        $this->assertIsInt($response);
        $this->assertEquals($result, $response);
    }

    public function test_find_card_by_id()
    {
        $this->model->id = 1;
        $this->model
            ->shouldReceive('findOrFail')
            ->andReturn($this->model);

        $response = $this->service->findCardById($this->model->id);
        $this->assertInstanceOf(Card::class, $response);
    }

    public function test_findByAttributes()
    {
        $attributes = [
            'id' => 1
        ];

        $this->model
            ->shouldReceive('where->first')
            ->andReturn($attributes);

        $response = $this->service->findByAttributes($attributes);
        $this->assertIsArray($response);
    }

    public function test_update_kumihan_count_card()
    {
        $card = m::mock(Card::class)->makePartial();
        $card->setRawAttributes(['style' => json_encode(['kumihan_count' => 3])]);
        $card->shouldReceive('save')->once();

        $response = $this->service->updateKumihanCountCard($card);

        $this->assertNull($response);
        $this->assertSame(4, $card->style['kumihan_count']);
    }

    public function test_export_pdf()
    {
        $this->model->id = 21;
        $this->model->print_background = 'print.tif';
        $this->model->style = [
            'width' => 100,
            'photo_finishing_flg' => 2,
            'photo_flg' => 1,
            'height' => 100,
            'type' => config('card.element_type.crop'),
            'x_coordinate' => 100,
            'y_coordinate' => 100,
            'angle' => 1.5,
        ];
        $this->model->print_complete_text = 'print.tif';
        $this->model->kumihan_id = 'KI200602164132997048';
        $this->model->image = 'screen.jpg';

        File::shouldReceive('exists')->andReturn(true);

        $element = m::mock(Element::class)->makePartial();
        $element->id = 1;
        $element->image = 'screen.jpg';
        $element->style = [
            'mask_id' => 1,
            'type' => config('card.element_type.crop'),
            'x_coordinate' => 100,
            'y_coordinate' => 100,
            'angle' => 1.5,
        ];
        $element->width = 100;
        $element->height = 100;

        $data = [
            'style' => [
                'clipDbId' => 0,
                'mask' => [
                    'name' => 'screen'
                ],
                'scale' => 1,
                'angle' => 1.5,
                'crop' => [
                    'width' => 100,
                    'height' => 100,
                    'x_coord' => 10,
                    'y_coord' => 10
                ],
            ],
            'image' => 'screen.jpg'
        ];

        $clipElements = [
            0 => [
                'style' => [
                    'mask' => [
                        'name' => 'screen.jpg'
                    ],
                    'x_coordinate' => 100,
                    'y_coordinate' => 100
                ]
            ]
        ];

        $this->model
            ->shouldReceive('getClipElements->keyBy->toArray')
            ->andReturn($clipElements);

        $this->model
            ->shouldReceive('getImageElements')
            ->andReturn(collect([$data]));

        File::shouldReceive('exists')->andReturn(false);

        Log::shouldReceive('error')->andReturn('');

        File::shouldReceive('isDirectory')->andReturn(false);

        File::shouldReceive('makeDirectory')->andReturn(true);

        $this->model
            ->shouldReceive('getMutiTypeElements')
            ->andReturn(collect([$element]));

        $this->model
            ->shouldReceive('pluck->toArray')
            ->andReturn(collect([$this->model]));

        $imgModel = m::mock(\App\Models\Image::class)->makePartial();
        $imgModel->id = 1;
        $imgModel->image = 'images/icon-stamp-1';

        $imgModel->shouldReceive('whereIn->pluck->toArray')->andReturn(collect($imgModel));

        $cardFolder = public_path('uploads/cards/0/');
        if (!File::isDirectory($cardFolder)) {
            File::makeDirectory($cardFolder, 0777, true, true);
        }

        File::shouldReceive('copy')->andReturn(
            storage_path('app/tests/preview.jpg'), sprintf('%spreview.jpg', $cardFolder)
        );

        $jpgPath = public_path() . config('path.upload.card') . 0 . '/' . 'preview.jpg';

        Image::shouldReceive('read->core->native')->andReturn(new \Imagick(realpath($jpgPath)));

        File::shouldReceive('isDirectory')->andReturn(false);

        File::shouldReceive('makeDirectory')->andReturn(true);

        $response = $this->service->exportPDF($this->model);
        $this->assertFalse($response);
    }

    public function test_export_pdf_true()
    {
        $this->model->id = 21;
        $this->model->print_background = 'print.tif';
        $this->model->style = [
            'width' => 171.3,
            'photo_finishing_flg' => 2,
            'photo_flg' => 2,
            'height' => 100,
            'type' => config('card.element_type.crop'),
            'x_coordinate' => 100,
            'y_coordinate' => 100,
            'angle' => 1.5,
            'designcolor_name' => 'test'
        ];
        $this->model->print_complete_text = 'print.tif';
        $this->model->kumihan_id = 'KI200602164132997048';
        $this->model->image = 'screen.jpg';

        File::shouldReceive('exists')->andReturn(true);

        $element = m::mock(Element::class)->makePartial();
        $element->id = 1;
        $element->image = 'screen.jpg';
        $element->style = [
            'mask_id' => 1,
            'type' => config('card.element_type.image'),
            'x_coordinate' => 100,
            'y_coordinate' => 100,
            'angle' => 1.5,
            'imageId' => 1,
        ];
        $element->width = 100;
        $element->height = 100;

        $data = [
            'style' => [
                'clipDbId' => 0,
                'mask' => [
                    'name' => 'screen'
                ],
                'scale' => 1,
                'angle' => 1.5,
                'crop' => [
                    'width' => 100,
                    'height' => 100,
                    'x_coord' => 10,
                    'y_coord' => 10
                ],
            ],
            'image' => 'screen.jpg'
        ];

        $clipElements = [
            0 => [
                'style' => [
                    'mask' => [
                        'name' => 'screen.jpg'
                    ],
                    'x_coordinate' => 100,
                    'y_coordinate' => 100
                ]
            ]
        ];

        $this->model
            ->shouldReceive('getClipElements->keyBy->toArray')
            ->andReturn($clipElements);

        $this->model
            ->shouldReceive('getImageElements')
            ->andReturn(collect([$data]));

        File::shouldReceive('exists')->andReturn(false);

        Log::shouldReceive('error')->andReturn('');

        File::shouldReceive('isDirectory')->andReturn(false);

        File::shouldReceive('makeDirectory')->andReturn(true);

        $this->model
            ->shouldReceive('getMutiTypeElements')
            ->andReturn(collect([$element]));

        $this->model
            ->shouldReceive('pluck->toArray')
            ->andReturn(collect([$this->model]));

        $imgModel = m::mock(\App\Models\Image::class)->makePartial();
        $imgModel->id = 1;
        $imgModel->image = 'images/icon-stamp-1';

        $imgModel->shouldReceive('whereIn->pluck->toArray')->andReturn(collect($imgModel));

        $cardFolder = public_path('uploads/cards/0/');
        if (!File::isDirectory($cardFolder)) {
            File::makeDirectory($cardFolder, 0777, true, true);
        }

        File::shouldReceive('copy')->andReturn(
            storage_path('app/tests/preview.jpg'), sprintf('%spreview.jpg', $cardFolder)
        );

        $jpgPath = public_path() . config('path.upload.card') . 0 . '/' . 'preview.jpg';

        Image::shouldReceive('read->core->native')->andReturn(new \Imagick(realpath($jpgPath)));

        File::shouldReceive('isDirectory')->andReturn(false);

        File::shouldReceive('makeDirectory')->andReturn(true);

        File::shouldReceive('exists')->andReturn(true);

        Log::shouldReceive('info')->andReturn('');

        $response = $this->service->exportPDF($this->model);
        $this->assertFalse($response);
    }

    public function test_export_pdf_fail_kumihan_error()
    {
        $this->model->id = 21;
        $this->model->print_background = 'print.tif';
        $this->model->style = [
            'width' => 171.3,
            'photo_finishing_flg' => 2,
            'photo_flg' => 2,
            'height' => 100,
            'type' => config('card.element_type.crop'),
            'x_coordinate' => 100,
            'y_coordinate' => 100,
            'angle' => 1.5,
            'designcolor_name' => 'test'
        ];
        $this->model->print_complete_text = 'print.tif';
        $this->model->kumihan_id = 'KI200602164132997048';
        $this->model->image = 'screen.jpg';
        $this->model->is_complete_image_created = 0;

        File::shouldReceive('exists')->andReturn(true);

        $result = [
            'status' => 'error',
            'message' => 'error'
        ];

        $this->kumihanService
            ->shouldReceive('getCompleteText')
            ->andReturn($result);

        Log::shouldReceive('error')->andReturn('');

        $response = $this->service->exportPDF($this->model);
        $this->assertFalse($response);
    }

    public function test_export_pdf_path_false()
    {
        $this->model->id = 21;
        $this->model->print_background = 'print.tif';
        $this->model->style = [
            'width' => 100,
            'photo_finishing_flg' => 1,
            'photo_flg' => 2,
            'height' => 100,
            'type' => config('card.element_type.crop'),
            'x_coordinate' => 100,
            'y_coordinate' => 100,
            'angle' => 1.5,
            'designcolor_name' => Card::IS_BW_DESIGN
        ];
        $this->model->print_complete_text = 'print.tif';
        $this->model->kumihan_id = 'KI200602164132997048';
        $this->model->image = 'screen.jpg';

        File::shouldReceive('exists')->andReturn(true);

        $element = m::mock(Element::class)->makePartial();
        $element->id = 1;
        $element->image = 'screen.jpg';
        $element->style = [
            'mask_id' => 1,
            'type' => config('card.element_type.crop'),
            'x_coordinate' => 100,
            'y_coordinate' => 100,
            'angle' => 1.5,
        ];
        $element->width = 100;
        $element->height = 100;

        $data = [
            'style' => [
                'clipDbId' => 0,
                'mask' => [
                    'name' => 'screen'
                ],
                'scale' => 1,
                'angle' => 1.5,
                'crop' => [
                    'width' => 100,
                    'height' => 100,
                    'x_coord' => 10,
                    'y_coord' => 10
                ],
            ],
            'image' => 'screen.jpg'
        ];

        $clipElements = [
            0 => [
                'style' => [
                    'mask' => [
                        'name' => 'screen.jpg'
                    ],
                    'x_coordinate' => 100,
                    'y_coordinate' => 100
                ]
            ]
        ];

        $this->model
            ->shouldReceive('getClipElements->keyBy->toArray')
            ->andReturn($clipElements);

        $this->model
            ->shouldReceive('getImageElements')
            ->andReturn(collect([$data]));

        File::shouldReceive('exists')->andReturn(false);

        Log::shouldReceive('error')->andReturn('');

        File::shouldReceive('isDirectory')->andReturn(false);

        File::shouldReceive('makeDirectory')->andReturn(true);

        $this->model
            ->shouldReceive('getMutiTypeElements')
            ->andReturn(collect([$element]));

        $this->model
            ->shouldReceive('pluck->toArray')
            ->andReturn(collect([$this->model]));

        $imgModel = m::mock(\App\Models\Image::class)->makePartial();
        $imgModel->id = 1;
        $imgModel->image = 'images/icon-stamp-1';

        $imgModel->shouldReceive('whereIn->pluck->toArray')->andReturn(collect($imgModel));

        $cardFolder = public_path('uploads/cards/0/');
        if (!File::isDirectory($cardFolder)) {
            File::makeDirectory($cardFolder, 0777, true, true);
        }

        File::shouldReceive('copy')->andReturn(
            storage_path('app/tests/preview.jpg'), sprintf('%spreview.jpg', $cardFolder)
        );

        $jpgPath = public_path() . config('path.upload.card') . 0 . '/' . 'preview.jpg';

        Image::shouldReceive('read->core->native')->andReturn(new \Imagick(realpath($jpgPath)));

        File::shouldReceive('isDirectory')->andReturn(false);

        File::shouldReceive('makeDirectory')->andReturn(true);

        File::shouldReceive('exists')->andReturn(false);

        Log::shouldReceive('error')->andReturn('');

        $response = $this->service->exportPDF($this->model);
        $this->assertFalse($response);
    }

    public function test_export_pdf_background_path_fail()
    {
        $this->model->id = 1;
        $this->model->print_background = 'print.tif';

        $response = $this->service->exportPDF($this->model);
        $this->assertFalse($response);
    }

    public function test_copy_card_files_to_mounted_storage()
    {
        $this->model->id = 1;
        $this->model->kumihan_id = $this->kumihanId;
        $this->model->image_preview = 'preview.jpg';

        $testFile = storage_path('app/tests/preview.jpg');
        $folder = sprintf(config('common.card_folder'), $this->model->id);
        $file = $this->model->image_preview;
        $tmpFolder = storage_path('app/tmp');
        config(['card_user_photo' => $tmpFolder . '/%s/PH_%s-%s.%s']);

        File::makeDirectory($folder, 0755, true, true);
        File::copy($testFile, $folder . $file);

        $element = m::mock(Element::class)->makePartial();
        $element->id = 1;
        $element->image = $this->model->image_preview;
        $element->style = [
            'mask_id' => 1
        ];

        $this->model
            ->shouldReceive('getImageElements')
            ->andReturn(collect([$element]));

        $response = $this->invokeMethod($this->service, 'copyCardFilesToMountedStorage', [$this->model]);
        File::deleteDirectory($folder);

        $this->assertFalse($response);
    }

    public function test_copy_card_thumbnail_to_mounted_storage()
    {
        $this->model->image_preview = 'preview.jpg';

        File::shouldReceive('exists')->andReturn(true);

        File::shouldReceive('extension')->andReturn('jpg');

        Log::shouldReceive('debug')->andReturn('');

        File::shouldReceive('copy')->andReturn(true);

        Log::shouldReceive('info')->andReturn('');

        $response = $this->invokeMethod($this->service, 'copyCardThumbnailToMountedStorage', [$this->model]);
        $this->assertTrue($response);
    }

    public function test_copy_naire_atena_card_thumbnail_to_mounted_storage()
    {
        $this->model->image_preview = 'preview.jpg';
        $this->model->shouldReceive('getHasAtenaAttribute')->andReturn(true);

        File::shouldReceive('exists')->andReturn(true);

        File::shouldReceive('extension')->andReturn('jpg');

        Log::shouldReceive('debug')->andReturn('');

        File::shouldReceive('copy')->andReturn(true);

        Log::shouldReceive('info')->andReturn('');

        $response = $this->invokeMethod($this->service, 'copyCardThumbnailToMountedStorage', [$this->model]);
        $this->assertTrue($response);
    }

    public function test_update_address_print_num()
    {
        $this->model->shouldReceive('receivers->wherePivot->count')->andReturn($this->model);
        $this->model->shouldReceive('save')->andReturn(true);

        $response = $this->service->updateAddressPrintNum($this->model);
        $this->assertNull($response);
    }

    public function test_copy_card_user_photo_to_mounted_storage_true()
    {
        $this->model->id = 1;
        $this->model->kumihan_id = $this->kumihanId;
        $this->model->has_atena = 1;

        $element = m::mock(Element::class)->makePartial();
        $element->id = 1;
        $element->image = 'test.jpg';
        $element->style = [
            'mask_id' => 1
        ];

        $this->model
            ->shouldReceive('getImageElements')
            ->andReturn(collect([$element]));

        File::shouldReceive('extension')->andReturn('jpg');

        Log::shouldReceive('debug')->andReturn('');

        File::shouldReceive('copy')->andReturn(true);

        Log::shouldReceive('info')->andReturn('');

        $response = $this->invokeMethod($this->service, 'copyCardUserPhotoToMountedStorage', [$this->model]);
        $this->assertTrue($response);
    }

    /**
     * @expectedException \Exception
     */
    public function test_copy_card_user_photo_to_mounted_storage_throw_exception()
    {
        $this->model->id = 1;
        $this->model->kumihan_id = $this->kumihanId;

        $element = m::mock(Element::class)->makePartial();
        $element->id = 1;
        $element->image = 'test.jpg';
        $element->style = [
            'mask_id' => 1
        ];

        $this->model
            ->shouldReceive('getImageElements')
            ->andReturn(collect([$element]));

        File::shouldReceive('extension')->andReturn('jpg');

        Log::shouldReceive('debug')->andReturn('');

        File::shouldReceive('copy')->andReturn(false);

        Log::shouldReceive('info')->andReturn('');

        $this->invokeMethod($this->service, 'copyCardUserPhotoToMountedStorage', [$this->model]);
    }

    public function test_duplicate_card()
    {
        $request = new DuplicateTemporaryCardRequest([
            'typesetting_id' => $this->kumihanId,
        ]);

        $this->model->id = 1;
        $this->model->kumihan_id = $this->kumihanId;
        $this->model->atena_kumihan_id = $this->kumihanId;

        $this->model->shouldReceive('where->firstOrFail')
            ->andReturn($this->model);
        $this->model->shouldReceive('replicate')
            ->andReturn($this->model);
        $this->model->shouldReceive('save')
            ->andReturn($this->model);
        $this->model->shouldReceive('update')
            ->andReturn($this->model);

        $kumihanService = $this->mock(KumihanService::class);
        $kumihanService->shouldReceive('duplicateKumihanForMydesign')
            ->andReturn('OK');
        app()->instance(KumihanService::class, $kumihanService);

        $this->model->shouldReceive('replicateRelations')
            ->andReturn(true);
        $this->model->shouldReceive('replicateFolder')
            ->andReturn(true);

        $response = $this->service->duplicateCard($this->model->kumihan_id);
        $this->assertInstanceOf(Card::class, $response);
    }

    /**
     * @expectedException Exception
     */
    public function test_duplicate_card_exception()
    {
        $request = new DuplicateTemporaryCardRequest([
            'typesetting_id' => $this->kumihanId,
        ]);

        $this->model->id = 1;
        $this->model->kumihan_id = $this->kumihanId;

        $this->model->shouldReceive('where->firstOrFail')
            ->andReturn($this->model);
        $this->model->shouldReceive('replicate')
            ->andReturn($this->model);
        $this->model->shouldReceive('save')
            ->andReturn($this->model);
        $this->model->shouldReceive('update')
            ->andReturn($this->model);

        $kumihanService = $this->mock(KumihanService::class);
        $kumihanService->shouldReceive('duplicateKumihanForMydesign')
            ->andReturn('DL-1');
        app()->instance(KumihanService::class, $kumihanService);

        $response = $this->service->duplicateCard($this->model->kumihan_id);
    }

    /**
     * @expectedException Exception
     */
    public function test_duplicate_card_exception_commom_error()
    {
        $request = new DuplicateTemporaryCardRequest([
            'typesetting_id' => $this->kumihanId,
        ]);

        $this->model->id = 1;
        $this->model->kumihan_id = $this->kumihanId;

        $this->model->shouldReceive('where->firstOrFail')
            ->andReturn($this->model);
        $this->model->shouldReceive('replicate')
            ->andReturn($this->model);
        $this->model->shouldReceive('save')
            ->andReturn($this->model);
        $this->model->shouldReceive('update')
            ->andReturn($this->model);

        $kumihanService = $this->mock(KumihanService::class);
        $kumihanService->shouldReceive('duplicateKumihanForMydesign')
            ->andReturn('DL-0');
        app()->instance(KumihanService::class, $kumihanService);

        $response = $this->service->duplicateCard($this->model->kumihan_id);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Tests for CardService functions called from CardController::preview()
    // ──────────────────────────────────────────────────────────────────────

    public function test_get_session_exchange_returns_default_when_no_session()
    {
        $response = $this->service->getSessionExchange($this->hashid);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('kousei_email', $response);
        $this->assertArrayHasKey('oemail', $response);
        $this->assertArrayHasKey('kousei_flg', $response);
        $this->assertArrayHasKey('accept_preview', $response);
        $this->assertArrayHasKey('no_deal_reason', $response);
        $this->assertEquals('', $response['kousei_email']);
        $this->assertEquals(1, $response['accept_preview']);
    }

    public function test_get_session_exchange_returns_session_data_when_present()
    {
        $exchangeData = [
            'kousei_email' => 'test@example.com',
            'oemail' => 'order@example.com',
            'kousei_flg' => 1,
            'accept_preview' => 0,
            'no_deal_reason' => 'reason',
        ];

        session(["exchange_{$this->hashid}" => $exchangeData]);

        $response = $this->service->getSessionExchange($this->hashid);

        $this->assertEquals($exchangeData, $response);
        $this->assertEquals('test@example.com', $response['kousei_email']);
        $this->assertEquals(1, $response['kousei_flg']);
    }

    public function test_check_support_atena_throws_when_area_agency_not_found()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Area agency was not found.');

        $this->areaAgencyRepository
            ->shouldReceive('getAreaAgencyByAreaIdAndAgencyId')
            ->with('A1', 'LS01')
            ->andReturnNull();

        Log::shouldReceive('error')->andReturn(null);

        $this->service->checkSupportAtena([
            'design_id'   => 'P01',
            'material_id' => 'M01',
            'agency_id'   => 'LS01',
            'site_id'     => 'A1',
        ], $this->hashid);
    }

    public function test_check_support_atena_returns_no_atena_when_atena_support_empty()
    {
        $areaAgency = (object) [
            'atena_support' => null,
            'atena_end_date' => null,
        ];

        $this->areaAgencyRepository
            ->shouldReceive('getAreaAgencyByAreaIdAndAgencyId')
            ->andReturn($areaAgency);

        $response = $this->service->checkSupportAtena([
            'design_id'   => 'P01',
            'material_id' => 'M01',
            'agency_id'   => 'LS01',
            'site_id'     => 'A1',
        ], $this->hashid);

        $this->assertIsArray($response);
        $this->assertFalse($response['has_atena']);
    }

    public function test_check_support_atena_throws_when_design_not_found()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Design was not found.');

        $areaAgency = m::mock(\App\Models\AreaAgency::class)->makePartial();
        $areaAgency->atena_support = defined('App\\Models\\AreaAgency::ATENA_SUPPORT')
            ? \App\Models\AreaAgency::ATENA_SUPPORT[0]
            : 1;
        $areaAgency->atena_end_date = null;

        $this->areaAgencyRepository
            ->shouldReceive('getAreaAgencyByAreaIdAndAgencyId')
            ->andReturn($areaAgency);

        $this->designRepository
            ->shouldReceive('findDesignByMaterialIdAndDesignId')
            ->andReturnNull();

        Log::shouldReceive('error')->andReturn(null);

        $this->service->checkSupportAtena([
            'design_id'   => 'P01',
            'material_id' => 'M01',
            'agency_id'   => 'LS01',
            'site_id'     => 'A1',
        ], $this->hashid);
    }

    public function test_check_support_atena_returns_no_atena_when_end_date_expired()
    {
        $atenaSupport = defined('App\\Models\\AreaAgency::ATENA_SUPPORT')
            ? \App\Models\AreaAgency::ATENA_SUPPORT[0]
            : 1;

        $areaAgency = m::mock(\App\Models\AreaAgency::class)->makePartial();
        $areaAgency->atena_support = $atenaSupport;
        $areaAgency->atena_end_date = '2000-01-01 00:00:00'; // past date → expired

        $this->areaAgencyRepository
            ->shouldReceive('getAreaAgencyByAreaIdAndAgencyId')
            ->andReturn($areaAgency);

        $response = $this->service->checkSupportAtena([
            'design_id'   => 'P01',
            'material_id' => 'M01',
            'agency_id'   => 'LS01',
            'site_id'     => 'A1',
        ], $this->hashid);

        $this->assertIsArray($response);
        $this->assertFalse($response['has_atena']);
    }

    public function test_check_support_atena_returns_no_atena_when_design_not_support_atena()
    {
        $atenaSupport = defined('App\\Models\\AreaAgency::ATENA_SUPPORT')
            ? \App\Models\AreaAgency::ATENA_SUPPORT[0]
            : 1;

        $areaAgency = m::mock(\App\Models\AreaAgency::class)->makePartial();
        $areaAgency->atena_support = $atenaSupport;
        $areaAgency->atena_end_date = null;

        $this->areaAgencyRepository
            ->shouldReceive('getAreaAgencyByAreaIdAndAgencyId')
            ->andReturn($areaAgency);

        $designAtenaSupport = defined('App\\Models\\Design::ATENA_SUPPORT')
            ? \App\Models\Design::ATENA_SUPPORT
            : 1;

        $design = (object) ['atena_support' => $designAtenaSupport + 1]; // wrong value → not supported

        $this->designRepository
            ->shouldReceive('findDesignByMaterialIdAndDesignId')
            ->andReturn($design);

        $response = $this->service->checkSupportAtena([
            'design_id'   => 'P01',
            'material_id' => 'M01',
            'agency_id'   => 'LS01',
            'site_id'     => 'A1',
        ], $this->hashid);

        $this->assertIsArray($response);
        $this->assertFalse($response['has_atena']);
    }

    public function test_check_support_atena_returns_has_atena_true_when_all_valid()
    {
        $atenaSupport = defined('App\\Models\\AreaAgency::ATENA_SUPPORT')
            ? \App\Models\AreaAgency::ATENA_SUPPORT[0]
            : 1;

        $areaAgency = m::mock(\App\Models\AreaAgency::class)->makePartial();
        $areaAgency->atena_support = $atenaSupport;
        $areaAgency->atena_end_date = null;

        $this->areaAgencyRepository
            ->shouldReceive('getAreaAgencyByAreaIdAndAgencyId')
            ->andReturn($areaAgency);

        $designAtenaSupport = defined('App\\Models\\Design::ATENA_SUPPORT')
            ? \App\Models\Design::ATENA_SUPPORT
            : 1;

        $design = (object) ['atena_support' => $designAtenaSupport]; // correct value

        $this->designRepository
            ->shouldReceive('findDesignByMaterialIdAndDesignId')
            ->andReturn($design);

        $response = $this->service->checkSupportAtena([
            'design_id'   => 'P01',
            'material_id' => 'M01',
            'agency_id'   => 'LS01',
            'site_id'     => 'A1',
        ], $this->hashid);

        $this->assertIsArray($response);
        $this->assertTrue($response['has_atena']);
        $this->assertEquals('', $response['redirect_url']);
    }

    public function test_get_card_folder_returns_string()
    {
        if (!function_exists('get_card_folder')) {
            function get_card_folder($card) { return '/tmp/cards/' . ($card->id ?? 0) . '/'; }
        }

        $this->model->id = 1;

        $response = $this->service->getCardFolder($this->model);

        $this->assertIsString($response);
    }

    public function test_export_atena_preview_returns_true_when_card_has_no_atena()
    {
        $this->model->id = 99;
        $this->model->has_atena = false;

        $this->model
            ->shouldReceive('findOrFail')
            ->andReturn($this->model);

        $response = $this->service->exportAtenaPreview($this->model->id);

        $this->assertTrue($response);
    }

    public function test_export_atena_preview_returns_false_on_exception()
    {
        $this->model->id = 99;
        $this->model
            ->shouldReceive('findOrFail')
            ->andThrow(new Exception('test error'));

        Log::shouldReceive('error')->andReturn(null);

        $response = $this->service->exportAtenaPreview($this->model->id);

        $this->assertFalse($response);
    }

    public function test_export_preview_returns_true_when_has_atena_and_preview_image_exists()
    {
        $this->model->style = ['width' => 100, 'height' => 100];
        $this->model->shouldReceive('save')->andReturn(true);
        $this->service = \Mockery::mock(CardService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->service->shouldReceive('findCardById')->andReturn($this->model);
        if (!function_exists('get_card_folder')) {
            function get_card_folder($card) { return '/tmp/cards/' . ($card->id ?? 0) . '/'; }
        }

        \Mockery::close(); // Reset all previous mocks
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        $this->model->id = 99;
        $this->model->has_atena = true;

        $this->model
            ->shouldReceive('findOrFail')
            ->andReturn($this->model);

        config(['card.image_name.preview' => 'preview']);
        $jpgPath = '/tmp/cards/99/preview.jpg';
        File::shouldReceive('exists')->andReturnUsing(function($path) use ($jpgPath) {
            return $path === $jpgPath;
        });

        Log::shouldReceive('error')->andReturn(null);

        $response = $this->service->exportPreview($this->model->id);

        $this->assertTrue($response);
    }

    public function test_export_preview_returns_true_on_successful_export()
    {
        $this->model->style = ['width' => 100, 'height' => 100];
        $this->model->shouldReceive('save')->andReturn(true);
        $this->service = \Mockery::mock(CardService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->service->shouldReceive('findCardById')->andReturn($this->model);
        \Mockery::close(); // Reset all previous mocks
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        if (!function_exists('get_session_id')) {
            function get_session_id() { return 'test-session-id'; }
        }

        if (!function_exists('get_card_folder')) {
            function get_card_folder($card) { return '/tmp/cards/' . ($card->id ?? 0) . '/'; }
        }

        config(['card.preview_dpi' => 1]);
        config(['card.image_name.preview' => 'preview']);

        $this->model->id = 99;
        $this->model->hash_id = 'testhash99';
        $this->model->has_atena = false;
        $this->model->image = 'bg.jpg';
        $this->model->style = ['width' => 100, 'height' => 100];

        $this->model
            ->shouldReceive('findOrFail')
            ->andReturn($this->model);

        session(["card_session_testhash99" => [
            'user_photo' => [],
            'stamp_photo' => null,
            'crop_photo' => null,
        ]]);

        $this->model->shouldReceive('getImageElements')->andReturn(collect([]));
        $this->model->shouldReceive('getClipElements->keyBy->toArray')->andReturn([]);

        $diskMock = m::mock();
        $diskMock->shouldReceive('exists')->andReturn(false);
        $diskMock->shouldReceive('get')->andReturn('fake-image-data');
        $diskMock->shouldReceive('put')->andReturn(true);
        Storage::shouldReceive('disk')->andReturn($diskMock);

        $imageManagerFake = new class {
            public function read($source) {
                $imagick = new \Imagick();
                $imagick->newPseudoImage(10, 10, 'xc:white');
                $imagick->setImageFormat('jpeg');
                return new class($imagick) {
                    public function __construct(public \Imagick $im) {}
                    public function core() { return $this; }
                    public function native() { return $this->im; }
                };
            }
        };
        \Intervention\Image\Laravel\Facades\Image::swap($imageManagerFake);

        $jpgPath = '/tmp/cards/99/preview.jpg';
        File::shouldReceive('exists')->andReturnUsing(function($path) use ($jpgPath) {
            return $path === $jpgPath ? false : null;
        });

        $this->imageRepository->shouldReceive('getListStampImage')->andReturn(collect([]));

        $this->model->shouldReceive('save')->andReturn(true);

        Log::shouldReceive('error')->andReturn(null);

        $response = $this->service->exportPreview($this->model->id);

        $this->assertTrue($response);
    }

    public function test_export_atena_preview_returns_true_when_has_atena_and_export_succeeds()
    {
        if (!function_exists('get_session_id')) {
            function get_session_id() { return 'test-session-id'; }
        }

        if (!function_exists('get_card_folder')) {
            function get_card_folder($card) { return '/tmp/cards/' . ($card->id ?? 0) . '/'; }
        }

        config(['card.preview_dpi' => 1]);

        $this->model->id = 99;
        $this->model->has_atena = true;

        $this->model
            ->shouldReceive('findOrFail')
            ->andReturn($this->model);

        $imageManagerFake = new class {
            public function read($source) {
                $imagick = new \Imagick();
                $imagick->newPseudoImage(10, 10, 'xc:white');
                $imagick->setImageFormat('jpeg');
                return new class($imagick) {
                    public function __construct(public \Imagick $im) {}
                    public function core() { return $this; }
                    public function native() { return $this->im; }
                };
            }
        };
        \Intervention\Image\Laravel\Facades\Image::swap($imageManagerFake);

        File::shouldReceive('exists')->andReturn(false);

        $diskMock = m::mock();
        $diskMock->shouldReceive('put')->andReturn(true);
        Storage::shouldReceive('disk')->andReturn($diskMock);

        $this->model->shouldReceive('save')->andReturn(true);

        Log::shouldReceive('error')->andReturn(null);

        $response = $this->service->exportAtenaPreview($this->model->id);

        $this->assertTrue($response);
    }
}
