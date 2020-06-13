<?php

use PHPUnit\Framework\TestCase;
use Overtrue\Socialite\Providers\WeChat;

class WechatTest extends TestCase
{
    public function testWeChatProviderHasCorrectlyRedirectResponse()
    {
        $response = (new WeChat([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/socialite/callback.php',
        ]))->redirect();

        $this->assertStringStartsWith('https://open.weixin.qq.com/connect/qrconnect', $response);
        $this->assertMatchesRegularExpression('/redirect_uri=http%3A%2F%2Flocalhost%2Fsocialite%2Fcallback.php/', $response);
    }

    public function testWeChatProviderTokenUrlAndRequestFields()
    {
        $provider = new WeChat([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/socialite/callback.php',
        ]);

        $this->assertSame('https://api.weixin.qq.com/sns/oauth2/access_token', $provider->tokenUrl());
        $this->assertSame([
            'appid' => 'client_id',
            'secret' => 'client_secret',
            'code' => 'iloveyou',
            'grant_type' => 'authorization_code',
        ], $provider->tokenFields('iloveyou'));

        $this->assertSame([
            'appid' => 'client_id',
            'redirect_uri' => 'http://localhost/socialite/callback.php',
            'response_type' => 'code',
            'scope' => 'snsapi_login',
            'state' => 'wechat-state',
            'connect_redirect' => 1,
        ], $provider->withState('wechat-state')->codeFields());
    }

    public function testOpenPlatformComponent()
    {
        $provider = new WeChat([
            'client_id' => 'client_id',
            'client_secret' => null,
            'redirect' => 'redirect-url',
        ]);
        $this->assertSame([
            'appid' => 'client_id',
            'redirect_uri' => 'redirect-url',
            'response_type' => 'code',
            'scope' => 'snsapi_base',
            'state' => 'state',
            'connect_redirect' => 1,
            'component_appid' => 'component-app-id',
        ], $provider->withState('state')->codeFields());

        $this->assertSame([
            'appid' => 'client_id',
            'component_appid' => 'component-app-id',
            'component_access_token' => 'token',
            'code' => 'simcode',
            'grant_type' => 'authorization_code',
        ], $provider->tokenFields('simcode'));

        $this->assertSame('https://api.weixin.qq.com/sns/oauth2/component/access_token', $provider->tokenUrl());
    }

    public function testOpenPlatformComponentWithCustomParameters()
    {
        $provider = new WeChat([
            'client_id' => 'client_id',
            'client_secret' => null,
            'redirect' => 'redirect-url',
        ]);
        $provider->with(['foo' => 'bar']);

        $fields = $provider->withState('wechat-state')->codeFields();

        $this->assertArrayHasKey('foo', $fields);
        $this->assertSame('bar', $fields['foo']);
    }
}
