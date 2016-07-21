<?php
/**
 * Parser for all MNIST files
 */
class MNistParser {

    CONST IMAGE_TRAIN = 0;
    CONST LABEL_TRAIN = 1;
    CONST IMAGE_TEST  = 2;
    CONST LABEL_TEST  = 3;

    protected $binDataTrainImages = array(
        'magic' => null,
        'num'   => null,
        'cols'  => null,
        'rows'  => null
    );

    protected $dataTrainLabels   = array(
    );

    protected $trainLabelData = array();

    protected $trainImageHandler = null;
    protected $trainLabelHandler = null;

    public function __construct() {

        $this->trainImageHandler  = fopen('train-images-idx3-ubyte', 'rb');
        $data  = fread($this->trainImageHandler , 4);
        $this->binDataTrainImages['magic'] = unpack("h*", $data);

        $data = fread($this->trainImageHandler , 4);
        $head = unpack("h*", $data);
        $head = $head[1];
        $head = $this->flipbyte($head);
        $this->binDataTrainImages['num'] = hexdec($head);

        $data = fread($this->trainImageHandler , 4);
        $head = unpack("h*", $data);
        $head = $head[1];
        $head = $this->flipbyte($head);
        $this->binDataTrainImages['rows'] = hexdec($head);

        $data = fread($this->trainImageHandler , 4);
        $head = unpack("h*", $data);
        $head = $head[1];
        $head = $this->flipbyte($head);
        $this->binDataTrainImages['cols'] = hexdec($head);


        // train label
        $this->trainLabelHandler = fopen('train-labels-idx1-ubyte', 'rb');
        $data  = fread($this->trainLabelHandler, 4);
        $magic = unpack("h*", $data);

        $data = fread($this->trainLabelHandler, 4);
        $head = unpack("h*", $data);
        $head = $head[1];
        $head = $this->flipbyte($head);
        $num  = hexdec($head);

        $this->dataTrainLabels['num'] = $num;

        $this->trainLabelData[] = 'STARTELEMENT';

        for ($i = 0; $i < $num; $i++) {
            $data   = fread($this->trainLabelHandler, 1);
            $unpack = unpack("h*", $data);
            $unpack = $this->flipbyte($unpack[1]);
            $this->trainLabelData[] = $unpack;
        }
    }

    public function getTestImageNum($num) {

        $pixelCnt = ($this->binDataTrainImages['cols'] * $this->binDataTrainImages['rows']);
        $bytes    = ( ($num-1) * $pixelCnt )  + 16;
        fseek($this->trainImageHandler, $bytes);

        $gd     = imagecreatetruecolor($this->binDataTrainImages['rows'], $this->binDataTrainImages['cols']);
        $pixels = array();
        for ($i = 0; $i < $this->binDataTrainImages['rows']; $i++) {
            for ($i2 = 0; $i2 < $this->binDataTrainImages['cols']; $i2++) {
                $data   = fread($this->trainImageHandler, 1);
                $unpack = unpack("C1", $data);
                imagesetpixel($gd, $i2, $i, $unpack[1]);
                $pixels[$i][$i2] = $unpack[1];
            }
        }
        return $pixels;

    }

    public function getTrainLabels() {
        return $this->trainLabelData;
    }
    public function getTrainImageNum($unm) {

    }

    public function getTestLabelNum($unm) {

    }

    public function getTrainLabelNum($unm) {

    }

    public function flipbyte($hex) {
        $str = '';
        for ($i=0; $i<strlen($hex); $i+=2) {
            $sub = substr($hex, $i, 2);
            if (strlen($sub) > 0) {
                $sub = strrev($sub);
            }
            $str .= $sub;
        }
        return $str;
    }

}

$test   = new MNistParser('test');
$number = 1;

while ($number < 100) {

    $image  = $test->getTestImageNum($number);
    $labels = $test->getTrainLabels($number);

    echo "\nLABEL " . $labels[$number];
    for ($i = 0; $i< 28; $i++) {
        for ($i2 = 0; $i2< 28; $i2++) {
            if ($image[$i][$i2] == 0) {
                echo '.';
            } else {
                echo '*';
            }
        }
        echo "\n";
    }
    $number++;
    sleep(1);
}



