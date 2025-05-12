<?php

namespace jobseeker\Bundle\ToolBundle\Service;

class ImageMagick
{

    /**
     * 
     * @type const
     * @param return type
     */
    const RETURNARRAY = 0;
    const RETURNCSV = 1;

    /**
     * 
     * @type const
     * @param direction
     */
    const NONE = 'None';
    const FORGET = 'Forget';
    const NORTH = 'North';
    const EAST = 'East';
    const SOUTH = 'South';
    const WEST = 'West';
    const NORTH_EAST = 'NorthEast';
    const NORTH_WEST = 'NorthWest';
    const SOUTH_EAST = 'SouthEast';
    const SOUTH_WEST = 'SouthWest';
    const CENTER = 'Center';

    /**
     * 
     * @var string
     */
    private $source;

    /**
     * 
     * @var string
     */
    private $destination;

    /**
     * 
     * @var boolean
     */
    private $escapeChars = true;

    /**
     * 
     * @var string
     */
    protected $imageMagickPath;

    /**
     * 
     * @var integer
     */
    protected $imageQuality = 80;

    /**
     * 
     * @var boolean
     */
    protected $debug = false;

    /**
     * 
     * @var array
     */
    protected $log = array();

    /**
     * 
     * @var array
     */
    protected $history = array();

    /**
     *
     * @var string 
     */
    protected $fontSize = '12';

    /**
     *
     * @var boolean 
     */
    protected $font = false;

    /**
     *
     * @var string 
     */
    protected $color = '#000';

    /**
     *
     * @var boolean 
     */
    protected $background = false;

    /**
     *
     * @var string 
     */
    protected $gravity = self::CENTER;

    /**
     *
     * @var string 
     */
    protected $text = '';

    /**
     * 
     * @type integer
     */
    protected $alpha = 50;

    public function __construct($source = "", $destination = "")
    {
        $this->setSource($source);
        $this->setDestination($destination);
        $this->escapeChars = !( strtolower(substr(php_uname('s'), 0, 3)) === "win" );
    }

    public function setSource($source)
    {
        $this->source = str_replace(' ', '\ ', $source);

        return $this;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setDestination($destination)
    {
        $this->destination = str_replace(' ', '\ ', $destination);

        return $this;
    }

    public function getDestination()
    {
        if ("" === $this->destination) {
            $source = $this->getSource();
            $ext = end(explode('.', $source));
            $this->destination = dirname($source) . '/' . md5(microtime()) . '.' . $ext;
        }

        return $this->destination;
    }

    protected function setImageMagickPath($path)
    {
        if ($path != '') {
            if (strpos($path, '/') < strlen($path)) {
                $path .= '/';
            }
        }
        $this->imageMagickPath = str_replace(' ', '\ ', $path);
    }

    protected function getImageMagickPath()
    {
        return $this->imageMagickPath;
    }

    public function setImageQuality($value)
    {
        $this->imageQuality = intval($value);

        return $this;
    }

    public function getImageQuality()
    {
        return $this->imageQuality;
    }

    public function setHistory($path)
    {
        $this->history[] = $path;

        return $this;
    }

    public function getHistory($type = null)
    {
        switch ($type) {
            case self::RETURNCSV:
                return explode(',', array_unique($this->history));
            case self::RETURNARRAY :
            default:
                return array_unique($this->history);
        }
    }

    public function clearHistory()
    {
        $this->history = array();
    }

    public function getLog()
    {
        return $this->log;
    }

    public function getBinary($binName)
    {
        return $this->getImageMagickPath() . $binName;
    }

    public function execute($cmd)
    {
        $ret = null;
        $out = array();

        if ($this->escapeChars) {
            $cmd = str_replace('(', '\(', $cmd);
            $cmd = str_replace(')', '\)', $cmd);
        }

        exec($cmd . ' 2>&1', $out, $ret);

        if ($ret != 0) {
            if ($this->debug) {
                trigger_error(new \Exception('Error executing "' . $cmd . '" <br>return code: ' . $ret . ' <br>command output :"' . implode("<br>", $out) . '"'), E_USER_NOTICE);
            }
        }

        $this->log[] = array(
            'cmd' => $cmd,
            'return' => $ret,
            'output' => $out
        );

        return $ret;
    }

    public function setFontSize($fontSize)
    {
        $this->fontSize = $fontSize;

        return $this;
    }

    public function getFontSize()
    {
        return $this->fontSize;
    }

    public function setFont($font)
    {
        $this->font = $font;

        return $this;
    }

    public function getFont()
    {
        return $this->font;
    }

    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setBackground($background)
    {
        $this->background = $background;

        return $this;
    }

    public function getBackground()
    {
        return $this->background;
    }

    public function setGravity($gravity)
    {
        $this->gravity = $gravity;

        return $this;
    }

    public function getGravity()
    {
        return $this->gravity;
    }

    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setAlpha($alpha)
    {
        $this->alpha = (int) $alpha;

        return $this;
    }

    public function getAlpha()
    {
        return $this->alpha;
    }

    public function resize($width, $height = 0, $exactDimentions = false)
    {
        $modifier = $exactDimentions ? '!' : '>';
        $width = $width == 0 ? '' : $width;
        $height = $height == 0 ? '' : $height;

        $cmd = $this->getBinary('convert');
        $cmd .= ' -scale "' . $width . 'x' . $height . $modifier;
        $cmd .= '" -quality ' . $this->getImageQuality();
        $cmd .= ' -strip ';
        $cmd .= ' "' . $this->getSource() . '" "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function resizeExactly($width, $height)
    {

        list($w, $h) = $this->getInfo($this->getSource());

        if ($w > $h) {
            $h = $height;
            $w = 0;
        } else {
            $h = 0;
            $w = $width;
        }

        return $this->resize($w, $h)->crop($width, $height);
    }

    public function onTheFly($imageUrl, $width, $height, $exactDimentions = false, $webPath = '', $physicalPath = '')
    {
        $basePath = str_replace($webPath, $physicalPath, dirname($imageUrl));
        $sourceFile = $basePath . '/' . basename($imageUrl);

        $thumbnailFile = $basePath . '/' . $width . '_' . $height . '_' . basename($imageUrl);

        $this->setSource($sourceFile);
        $this->setDestination($thumbnailFile);

        if (!file_exists($thumbnailFile)) {
            $this->resize($width, $height, $exactDimentions);
        }

        if (!file_exists($thumbnailFile)) {
            $thumbnailFile = $sourceFile;
        }

        return str_replace($physicalPath, $webPath, $thumbnailFile);
    }

    public function getInfo($file = '')
    {
        if ($file == '') {
            $file = $this->getSource();
        }

        return getimagesize($file);
    }

    public function getWidth($file = '')
    {
        list($width, $height, $type, $attr) = $this->getInfo($file);

        return $width;
    }

    public function getHeight($file = '')
    {
        list($width, $height, $type, $attr) = $this->getInfo($file);

        return $height;
    }

    public function getBits($file = '')
    {
        if ($file == '') {
            $file = $this->getSource();
        }
        $info = getimagesize($file);

        return $info["bits"];
    }

    public function getMime($file = '')
    {
        if ($file == '') {
            $file = $this->getSource();
        }
        $info = getimagesize($file);

        return $info["mime"];
    }

    public function __call($method, $args)
    {
        if (!method_exists($this, $method)) {
            throw new \Exception('Call to undefined method : ' . $method);
        }

        array_unshift($args, $this);
        $ret = call_user_func_array(array($this, $method), $args);

        if ($ret === false) {
            throw new \Exception('Error executing method "' . $method . "'");
        }

        return $ret;
    }

    public function crop($width, $height, $top = 0, $left = 0, $gravity = 'center')
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' ' . $this->getSource();

        if (($gravity != '') || ($gravity != self::NONE)) {
            $cmd .= ' -gravity ' . $gravity;
        }

        $cmd .= ' -crop ' . (int) $width . 'x' . (int) $height;
        $cmd .= '+' . $left . '+' . $top;
        $cmd .= ' ' . $this->getDestination();

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function convert()
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' -quality ' . $this->getImageQuality();
        $cmd .= ' "' . $this->getSource() . '"  "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function save()
    {
        return $this->convert();
    }

    public function darken($alpha = 50)
    {
        $percent = 100 - ($alpha ? : $this->getAlpha());

        list ($width, $height) = $this->getInfo();

        $cmd = $this->getBinary('composite');
        $cmd .= ' -blend  ' . $percent . ' ';
        $cmd .= '"' . $this->getSource() . '"';
        $cmd .= ' -size ' . $width . 'x' . $height . ' xc:black ';
        $cmd .= '-matte "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function brighten($alpha = 50)
    {
        $percent = 100 - ($alpha ? : $this->getAlpha());

        list ($width, $height) = $this->getInfo();

        $cmd = $this->getBinary('composite');
        $cmd .= ' -blend  ' . $percent . ' ';
        $cmd .= '"' . $this->getSource() . '"';
        $cmd .= ' -size ' . $width . 'x' . $height . ' xc:white ';
        $cmd .= '-matte "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function toGreyScale($enhance = 2)
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' -modulate 100,0 ';
        $cmd .= ' -sigmoidal-contrast ' . $enhance . 'x50%';
        $cmd .= ' -background "none" "' . $this->getSource() . '"';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function invertColors()
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' "' . $this->getSource() . '"';
        $cmd .= ' -negate ';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function sepia($tone = 90)
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' -sepia-tone ' . $tone . '% ';
        $cmd .= ' -modulate 100,50 ';
        $cmd .= ' -normalize ';
        $cmd .= ' -background "none" "' . $this->getSource() . '"';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function autoLevels()
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' -normalize ';
        $cmd .= ' -background "none" "' . $this->getSource() . '"';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function watermark($watermarkImage, $gravity = 'center', $transparency = 50)
    {
        $cmd = $this->getBinary('composite');
        $cmd .= ' -dissolve ' . $transparency;
        $cmd .= ' -gravity ' . $gravity;
        $cmd .= ' ' . $watermarkImage;
        $cmd .= ' "' . $this->getSource() . '"';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function tile(array $paths = null, $width = '', $height = 1)
    {
        if (is_null($paths)) {
            $paths = $this->getHistory(self::RETURNARRAY);
        }
        $cmd = $this->getBinary('montage');
        $cmd .= ' -geometry x+0+0 -tile ' . $width . 'x' . $height . ' ';
        $cmd .= implode(' ', $paths);
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function acquireFrame($file, $frames = 0)
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' "' . $file . '"[' . $frames . ']';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function roundCorners($i = 15)
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' "' . $this->getSource() . '"';
        $cmd .= ' ( +clone  -threshold -1 ';
        $cmd .= "-draw \"fill black polygon 0,0 0,$i $i,0 fill white circle $i,$i $i,0\" ";
        $cmd .= '( +clone -flip ) -compose Multiply -composite ';
        $cmd .= '( +clone -flop ) -compose Multiply -composite ';
        $cmd .= ') +matte -compose CopyOpacity -composite ';
        $cmd .= ' "' . $this->getDestination() . '"';


        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function dropShadow($color = '#000', $offset = 4, $transparency = 60, $top = 4, $left = 4)
    {

        $top = $top > 0 ? '+' . $top : $top;
        $left = $left > 0 ? '+' . $left : $left;

        $cmd = $this->getBinary('convert');
        $cmd .= ' -page ' . $top . $left . ' "' . $this->getSource() . '"';
        $cmd .= ' -matte ( +clone -background "' . $color . '" -shadow ' . $transparency . 'x4+' . $offset . '+' . $offset . ' ) +swap ';
        $cmd .= ' -background none -mosaic ';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function glow($color = '#827f00', $offset = 10, $transparency = 60)
    {
        $cmd = $this->getBinary('convert');

        $cmd .= ' "' . $this->getSource() . '" ';
        $cmd .= '( +clone  -background "' . $color . '"  -shadow ' . $transparency . 'x' . $offset . '-' . ($offset / 4) . '+' . ($offset / 4) . ' ) +swap -background none   -layers merge  +repage  ';

        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function fakePolaroid($rotate = 6, $borderColor = "#fff", $background = "none")
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' "' . $this->getSource() . '"';
        $cmd .= ' -bordercolor "' . $borderColor . '"  -border 6 -bordercolor grey60 -border 1 -background  "none"   -rotate ' . $rotate . ' -background  black  ( +clone -shadow 60x4+4+4 ) +swap -background  "' . $background . '"   -flatten';
        $cmd .= ' ' . $this->getDestination();

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function polaroid($format = null, $rotation = 6, $borderColor = "snow", $shaddowColor = "black", $background = "none")
    {
        if ($format === null) {
            $format = $this;
        }

        $cmd = $this->getBinary('convert');
        $cmd .= ' "' . $this->getSource() . '"';


        if ($format->getBackground() !== false) {
            $cmd .= ' -background "' . $format->getBackground() . '"';
        }

        if ($format->getColor() !== false) {
            $cmd .= ' -fill "' . $format->getColor() . '"';
        }

        if ($format->getFont() !== false) {
            $cmd .= ' -font ' . $format->getFont();
        }

        if ($format->getFontSize() !== false) {
            $cmd .= ' -pointsize ' . $format->getFontSize();
        }

        if ($format->getGravity() !== false) {
            $cmd .= ' -gravity ' . $format->getGravity();
        }

        if ($format->getText() != '') {
            $cmd .= ' -set caption "' . $format->getText() . '"';
        }

        $cmd .= ' -bordercolor "' . $borderColor . '" -background "' . $background . '" -polaroid ' . $rotation . ' -background "' . $background . '" -flatten ';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function border($borderColor = "#000", $borderSize = "1")
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' "' . $this->getSource() . '"';
        $cmd .= ' -bordercolor "' . $borderColor . '"  -border ' . $borderSize;
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function fromString($text = '', $format = null)
    {
        if ($format === null) {
            $format = $this;
        }

        $cmd = $this->getBinary('convert');

        if ($format->getBackground() !== false) {
            $cmd .= ' -background "' . $format->getBackground() . '"';
        }

        if ($format->getColor() !== false) {
            $cmd .= ' -fill "' . $format->getColor() . '"';
        }

        if ($format->getColor() !== false) {
            $cmd .= ' -fill "' . $format->getColor() . '"';
        }

        if ($format->getFontSize() !== false) {
            $cmd .= ' -pointsize ' . $format->getFontSize();
        }

        if (($format->getText() != '') && ($text = '')) {
            $text = $format->getText();
        }

        $cmd .= ' label:"' . $text . '"';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function rotate($degrees = 45)
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' -background "transparent" -rotate ' . $degrees;
        $cmd .= '  "' . $this->getSource() . '"';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function flipVertical()
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' -flip ';
        $cmd .= ' "' . $this->getSource() . '"';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function flipHorizontal()
    {
        $cmd = $this->getBinary('convert');
        $cmd .= ' -flop ';
        $cmd .= ' "' . $this->getSource() . '"';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

    public function reflection($size = 60, $transparency = 50)
    {
        $source = $this->getSource();

        $this->flipVertical();

        list($w, $h) = $this->getInfo($this->getDestination());
        $this->crop($w, $h * ($size / 100), 0, 0, self::NONE);

        $cmd = $this->getBinary('convert');
        $cmd .= ' "' . $this->getSource() . '"';
        $cmd .= ' ( -size ' . $w . 'x' . ( $h * ($size / 100)) . ' gradient: ) ';
        $cmd .= ' +matte -compose copy_opacity -composite ';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);

        $file = dirname($this->getDestination()) . '/' . uniqid() . '.png';

        $cmd = $this->getBinary('convert');
        $cmd .= '  -size ' . $w . 'x' . ( $h * ($size / 100)) . ' xc:none  ';
        $cmd .= ' "' . $file . '"';

        $this->execute($cmd);

        $cmd = $this->getBinary('composite');
        $cmd .= ' -dissolve ' . $transparency;
        $cmd .= ' "' . $this->getDestination() . '"';
        $cmd .= ' ' . $file;
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);

        unlink($file);

        $cmd = $this->getBinary('convert');
        $cmd .= ' "' . $source . '"';
        $cmd .= ' "' . $this->getDestination() . '"';
        $cmd .= ' -append ';
        $cmd .= ' "' . $this->getDestination() . '"';

        $this->execute($cmd);
        $this->setSource($this->getDestination());
        $this->setHistory($this->getDestination());

        return $this;
    }

}
