<?php
namespace Laradic\Assets;


use Illuminate\Contracts\Routing\UrlGenerator;

class Helper
{
    /** @var UrlGenerator */
    protected static $urlGenerator;

    /**
     * @return mixed
     */
    public static function getUrlGenerator()
    {
        return self::$urlGenerator;
    }

    /**
     * Set the urlGenerator value
     *
     * @param UrlGenerator $urlGenerator
     *
     * @return Helper
     */
    public static function setUrlGenerator(UrlGenerator $urlGenerator)
    {
        self::$urlGenerator = $urlGenerator;
    }

    /**
     * url method
     * @return UrlGenerator
     * @throws \Exception
     */
    protected static function url()
    {
        if ( static::$urlGenerator === null ) {
            if(function_exists('app') && app('url') instanceof UrlGenerator){
                static::setUrlGenerator(app('url'));
            } elseif(class_exists('Illuminate\Container\Container')){
                static::setUrlGenerator(forward_static_call('Illuminate\Container\Container::make', 'url'));
            } else {
                throw new \Exception('Could not resolve [url]');
            }

        }
        return self::$urlGenerator;
    }


    /**
     * Convert an HTML string to entities.
     *
     * @param string $value
     *
     * @return string
     */
    public static function entities($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Convert entities to HTML characters.
     *
     * @param string $value
     *
     * @return string
     */
    public static function decode($value)
    {
        return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    }


    /**
     * Generate a link to a JavaScript file.
     *
     * @param string $url
     * @param array  $attributes
     * @param bool   $secure
     *
     * @return string
     */
    public static function script($url, $attributes = [ ], $secure = null)
    {
        $attributes[ 'src' ] = static::url()->asset($url, $secure);

        return '<script' . static::attributes($attributes) . '></script>' . PHP_EOL;
    }

    /**
     * Generate a link to a CSS file.
     *
     * @param string $url
     * @param array  $attributes
     * @param bool   $secure
     *
     * @return string
     */
    public static function style($url, $attributes = [ ], $secure = null)
    {
        $defaults = [ 'media' => 'all', 'type' => 'text/css', 'rel' => 'stylesheet' ];

        $attributes = $attributes + $defaults;

        $attributes[ 'href' ] = static::url()->asset($url, $secure);

        return '<link' . static::attributes($attributes) . '>' . PHP_EOL;
    }

    /**
     * Generate an HTML image element.
     *
     * @param string $url
     * @param string $alt
     * @param array  $attributes
     * @param bool   $secure
     *
     * @return string
     */
    public static function image($url, $alt = null, $attributes = [ ], $secure = null)
    {
        $attributes[ 'alt' ] = $alt;

        return '<img src="' . static::url()->asset($url, $secure) . '"' . static::attributes($attributes) . '>';
    }

    /**
     * Generate a link to a Favicon file.
     *
     * @param string $url
     * @param array  $attributes
     * @param bool   $secure
     *
     * @return string
     */
    public static function favicon($url, $attributes = [ ], $secure = null)
    {
        $defaults = [ 'rel' => 'shortcut icon', 'type' => 'image/x-icon' ];

        $attributes = $attributes + $defaults;

        $attributes[ 'href' ] = static::url()->asset($url, $secure);

        return '<link' . static::attributes($attributes) . '>' . PHP_EOL;
    }

    /**
     * Generate a HTML link.
     *
     * @param string $url
     * @param string $title
     * @param array  $attributes
     * @param bool   $secure
     *
     * @return string
     */
    public static function link($url, $title = null, $attributes = [ ], $secure = null)
    {
        $url = static::url()->to($url, [ ], $secure);

        if ( is_null($title) || $title === false ) {
            $title = $url;
        }

        return '<a href="' . $url . '"' . static::attributes($attributes) . '>' . static::entities($title) . '</a>';
    }

    /**
     * Build an HTML attribute string from an array.
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function attributes($attributes)
    {
        $html = [ ];

        foreach ( (array)$attributes as $key => $value ) {
            $element = static::attributeElement($key, $value);

            if ( !is_null($element) ) {
                $html[] = $element;
            }
        }

        return count($html) > 0 ? ' ' . implode(' ', $html) : '';
    }

    /**
     * Build a single attribute element.
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    protected static function attributeElement($key, $value)
    {
        // For numeric keys we will assume that the key and the value are the same
        // as this will convert HTML attributes such as "required" to a correct
        // form like required="required" instead of using incorrect numerics.
        if ( is_numeric($key) ) {
            $key = $value;
        }

        if ( !is_null($value) ) {
            return $key . '="' . e($value) . '"';
        }
    }

}
