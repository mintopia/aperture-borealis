<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $client_id
 * @property string $client_secret
 * @property int $enabled
 * @property int $interval
 * @property int $expires_in
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DeviceCode> $codes
 * @property-read int|null $codes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereExpiresIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperClient {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $social_provider_id
 * @property int $client_id
 * @property \App\Enums\DeviceCodeStatus $status
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property string $device_code
 * @property string $user_code
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property \Illuminate\Support\Carbon|null $access_token_expires_at
 * @property string|null $external_id
 * @property string|null $email
 * @property string|null $nickname
 * @property string|null $avatar_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Client $client
 * @property-read \App\Models\SocialProvider $provider
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereAccessTokenExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereDeviceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereSocialProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceCode whereUserCode($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDeviceCode {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int $encrypted
 * @property int $hidden
 * @property mixed|null $value
 * @property string $validation
 * @property \App\Enums\SettingType $type
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting ordered(string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEncrypted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValidation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSetting {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $provider_class
 * @property int $enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SocialProviderSetting> $settings
 * @property-read int|null $settings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProvider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProvider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProvider query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProvider whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProvider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProvider whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProvider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProvider whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProvider whereProviderClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProvider whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSocialProvider {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $social_provider_id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property \App\Enums\SettingType $type
 * @property int $encrypted
 * @property int $hidden
 * @property string|null $validation
 * @property mixed|null $value
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SocialProvider $provider
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting ordered(string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereEncrypted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereSocialProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereValidation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialProviderSetting whereValue($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSocialProviderSetting {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $readonly
 * @property int $active
 * @property int $dark_mode
 * @property string $primary
 * @property string|null $secondary
 * @property string $nav_background
 * @property string $css
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereCss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereDarkMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereNavBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme wherePrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereReadonly($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereSecondary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Theme whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTheme {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nickname
 * @property string $email
 * @property int|null $social_provider_id
 * @property string|null $external_id
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property \Illuminate\Support\Carbon|null $access_token_expires_at
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAccessTokenExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSocialProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

