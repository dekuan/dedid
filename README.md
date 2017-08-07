# dekuan/dedid
An unique id generator for primary key of distributed database



# ALGORITHM

### Bit structure
It's a 64 bits bigint.

~~~
0 xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx x xxxxxx xxxxxx xx xxxxxxxx
~~~

### Details

Bits	| Usage	| Remark
----------|---------|--------
0			| Reserved| Always be 0
41			| Escaped Time (in millisecond)|0~69 years
6			| Data center|0~63
6			| Data node in the data center |0~63
10			| Random|0~1023




# Mark bits

### Center
~~~
0 00000000 00000000 00000000 00000000 00000000 0 111111 000000 00 00000000

00000000 00000000 00000000 00000000 00000000 00111111 00000000 00000000

00       00       00       00       00       3F       00       00
~~~

### Node
~~~
0 00000000 00000000 00000000 00000000 00000000 0 000000 111111 00 00000000

00000000 00000000 00000000 00000000 00000000 00000000 11111100 00000000

00       00       00       00       00       00       FC       00
~~~


### Escaped Time
~~~
0 11111111 11111111 11111111 11111111 11111111 1 000000 000000 00 00000000

01111111 11111111 11111111 11111111 11111111 11000000 00000000 00000000

7F       FF       FF       FF       FF       C0       00       00
~~~


### Random
~~~
0 00000000 00000000 00000000 00000000 00000000 0 000000 000000 11 11111111

00000000 00000000 00000000 00000000 00000000 00000000 00000011 11111111

00       00       00       00       00       00       03       FF
~~~


# HOW TO USE

### Create an new id normally

~~~
$cDId		= CDId::getInstance();
$nCenter	= 61;
$nNode		= 37;

$arrD		= [];
$nNewId	= $cDId->createId( $nCenter, $nNode, null, $arrD );

echo "new id = " . $nNewId . "\r\n";
print_r( $arrD );

~~~

##### output

~~~
new id = 98037672957548006
Array
(
    [center] => 61
    [node] => 37
    [time] => 23374002684
    [rand] => 486
)
~~~


### Create an new id with crc32 hash value

~~~
$cDId		= CDId::getInstance();
$nCenter	= 0;
$nNode		= 15;

$sSrc		= "dekuan";
$arrD		= [];
$nNewId	= $cDId->createId( $nCenter, $nNode, $sSrc, $arrD );

echo "new id = " . $nNewId . "\r\n";
print_r( $arrD );

~~~

##### output

~~~
new id = 99087647783270610
Array
(
    [center] => 0
    [node] => 15
    [time] => 23624336191
    [rand] => 210
)
~~~




### Parse an id for getting the details

~~~
$cDId		= CDId::getInstance();
$arrId		= $cDId->parseId( 98037672957548006 );
print_r( $arrId );

~~~

##### output

~~~
Array
(
    [center] => 61
    [node] => 37
    [time] => 23374002684
    [rand] => 486
)
~~~


# INSTALL
~~~
# composer require dekuan/dedid
~~~
For more information, please visit [https://packagist.org/packages/dekuan/dedid](https://packagist.org/packages/dekuan/dedid)
