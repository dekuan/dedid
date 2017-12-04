# dekuan/dedid
An unique id generator for primary key of distributed database.
This implementation of the algorithm was referenced by Twitter Snowflake,
but in the last 12 bits you can not only use the random numbers, but also get a hash value by your specified string.




* [简体中文版文档](README.chs.md) 


# ALGORITHM

### Bit structure
It's a 64 bits bigint.

~~~
0 xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx x xxxxx xxxxx xxxx xxxxxxxx
~~~

### Details

Position  | Length   | Usage	| Remark
----------|----------|----------|----------
0	| 1	| Reserved | Always be 0
1~41	| 41	| Escaped Time (in millisecond) | 0~69 years
42~46	| 5	| Number of data center | 0~31
47~51	| 5	| Number of data node in the data center | 0~31
52~63	| 12	| Random / Hash | 0~4095




# Bit marks

### Center
~~~
0 00000000 00000000 00000000 00000000 00000000 0 11111 00000 0000 00000000

00000000 00000000 00000000 00000000 00000000 00111110 00000000 00000000

00       00       00       00       00       3E       00       00
~~~

### Node
~~~
0 00000000 00000000 00000000 00000000 00000000 0 00000 11111 0000 00000000

00000000 00000000 00000000 00000000 00000000 00000001 11110000 00000000

00       00       00       00       00       01       F0       00
~~~


### Escaped Time
~~~
0 11111111 11111111 11111111 11111111 11111111 1 00000 00000 0000 00000000

01111111 11111111 11111111 11111111 11111111 11000000 00000000 00000000

7F       FF       FF       FF       FF       C0       00       00
~~~


### Random or Hash value
~~~
0 00000000 00000000 00000000 00000000 00000000 0 00000 00000 1111 11111111

00000000 00000000 00000000 00000000 00000000 00000000 00001111 11111111

00       00       00       00       00       00       0F       FF
~~~


# HOW TO USE

### Create an new id normally

~~~
$cDId		= CDId::getInstance();
$nCenter	= 0;
$nNode		= 1;

$arrD		= [];
$nNewId	= $cDId->createId( $nCenter, $nNode, null, $arrD );

echo "new id = " . $nNewId . "\r\n";
print_r( $arrD );

~~~

##### output

~~~
new id = 114654484990270790
Array
(
    [center] => 0
    [node] => 1
    [time] => 27335759399
    [rand] => 3398
)
~~~


### Create an new id with crc32 hash value by a specified string

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
new id = 114654631304370386
Array
(
    [center] => 0
    [node] => 1
    [time] => 27335794283
    [rand] => 2258
)
~~~




### Parse an id for getting the details

~~~
$cDId		= CDId::getInstance();
$arrId		= $cDId->parseId( 114654631304370386 );
print_r( $arrId );

~~~

##### output

~~~
Array
(
    [center] => 0
    [node] => 1
    [time] => 27335794283
    [rand] => 2258
)
~~~


# INSTALL
~~~
# composer require dekuan/dedid
~~~
For more information, please visit [https://packagist.org/packages/dekuan/dedid](https://packagist.org/packages/dekuan/dedid)
