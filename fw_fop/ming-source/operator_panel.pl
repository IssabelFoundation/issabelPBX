#!/usr/bin/perl -w
#  Flash Operator Panel.    http://www.asternic.org
#
#  Copyright (c) 2004 Nicolas Gudino. All rights reserved.
#
#  Nicolas Gudino <nicolas@house.com.ar>
#
#  This program is free software, distributed under the terms of
#  the GNU General Public License.
#
#  THIS SOFTWARE IS PROVIDED BY THE CONTRIBUTORS ``AS IS'' AND ANY EXPRESS OR
#  IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
#  OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  
#  IN NO EVENT SHALL THE CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
#  INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
#  NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
#  DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
#  OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING 
#  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, 
#  EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

use SWF qw(:ALL);
use SWF::Constants qw(:Text :Button :DisplayItem :Fill);

$stage_width  = 996;
$stage_height = 600;

SWF::setScale(2);
SWF::useSWFVersion(7);
my $movie = new SWF::Movie();
$movie->setDimension($stage_width * 10, $stage_height * 10);
$movie->setBackground(0xFF, 0xFF, 0xFF);
$movie->setRate(20);



$fuente="/usr/src/ming/util/fonts/Arial.fdb";
$fuente_nombre="Arial";

sub maketextfield {
	$name = shift;
	$font = shift;
	$str  = shift;
	
	$txtfield = new SWF::TextField( SWFTEXTFIELD_MULTILINE | SWFTEXTFIELD_WORDWRAP | SWFTEXTFIELD_USEFONT );
	$txtfield->setHeight(254);	
	$txtfield->setBounds(200,30);
	$txtfield->setName($name);
	$txtfield->setColor(0x00, 0x00, 0x00);
	$txtfield->setFont(new SWF::Font($font));
	$txtfield->addChars("\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f\x20\x21\x22\x23\x24\x25\x26\x27\x28\x29\x2a\x2b\x2c\x2d\x2e\x2f\x30\x31\x32\x33\x34\x35\x36\x37\x38\x39\x3a\x3b\x3c\x3d\x3e\x3f\x40\x41\x42\x43\x44\x45\x46\x47\x48\x49\x4a\x4b\x4c\x4d\x4e\x4f\x50\x51\x52\x53\x54\x55\x56\x57\x58\x59\x5a\x5b\x5c\x5d\x5e\x5f\x60\x61\x62\x63\x64\x65\x66\x67\x68\x69\x6a\x6b\x6c\x6d\x6e\x6f\x70\x71\x72\x73\x74\x75\x76\x77\x78\x79\x7a\x7b\x7c\x7d\x7e\x7f\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8a\x8b\x8c\x8d\x8e\x8f\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9a\x9b\x9c\x9d\x9e\x9f\xa0\xa1\xa2\xa3\xa4\xa5\xa6\xa7\xa8\xa9\xaa\xab\xac\xad\xae\xaf\xb0\xb1\xb2\xb3\xb4\xb5\xb6\xb7\xb8\xb9\xba\xbb\xbc\xbd\xbe\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf7\xf8\xf9\xfa\xfb\xfc\xfd\xfe\xff\x100\x101\x102\x103\x104\x105\x106\x107\x108\x109\x110\x111\x112\x113\x114\x115\x116\x116\x118\x119\x11a\x11b\x11c\x11d\x11e\x11f\x120\x121\x122\x123\x124\x125\x126\x127\x128\x129\x12a\x12b\x12c\x12d\x12e\x12f\x130\x131\x132\x133\x134\x135\x136\x137\x138\x139\x13a\x13b\x13c\x13d\x13e\x13f\x140\x141\x142\x143\x144\x145\x146\x147\x148\x149\x14a\x14b\x14c\x14d\x14e\x14f\x150\x151\x152\x153\x154\x155\x156\x157\x158\x159\x15a\x15b\x15c\x15d\x15e\x15f\x160\x161\x162\x163\x164\x165\x166\x167\x168\x169\x16a\x16b\x16c\x16d\x16e\x16f\x170\x171\x172\x173\x174\x175\x176\x177\x178\x179\x17a\x17b\x17c\x17d\x17e\x17f\x180\x181\x182\x183\x184\x185\x186\x187\x188\x189\x18a\x18b\x18c\x18d\x18e\x18f\x190\x191\x192\x193\x194\x195\x196\x197\x198\x199\x19a\x19b\x19c\x19d\x19e\x19f\x1a0\x1a1\x1a2\x1a3\x1a4\x1a5\x1a6\x1a7\x1a8\x1a9\x1aa\x1ab\x1ac\x1ad\x1ae\x1af\x1b0\x1b1\x1b2\x1b3\x1b4\x1b5\x1b6\x1b7\x1b8\x1b9\x1ba\x1bb\x1bc\x1bd\x1be\x1bf\x1c0\x1c1\x1c2\x1c3\x1c4\x1c5\x1c6\x1c7\x1c8\x1c9\x1ca\x1cb\x1cc\x1cd\x1ce\x1cf\x1d0\x1d1\x1d2\x1d3\x1d4\x1d5\x1d6\x1d7\x1d8\x1d9\x1da\x1db\x1dc\x1dd\x1de\x1df\x1e0\x1e1\x1e2\x1e3\x1e4\x1e5\x1e6\x1e7\x1e8\x1e9\x1ea\x1eb\x1ec\x1ed\x1ee\x1ef\x1f0\x1f1\x1f2\x1f3\x1f4\x1f5\x1f6\x1f7\x1f8\x1f9\x1fa\x1fb\x1fc\x1fd\x1fe\x1ff\x200\x201\x202\x203\x204\x205\x206\x207\x208\x209\x20a\x20b\x20c\x20d\x20e\x20f\x210\x211\x212\x213\x214\x215\x216\x217\x218\x219\x21a\x21b\x21c\x21d\x21e\x21f\x220\x221\x222\x223\x224\x225\x226\x227\x228\x229\x22a\x22b\x22c\x22d\x22e\x22f\x230\x231\x232\x233\x234\x235\x236\x237\x238\x239\x23a\x23b\x23c\x23d\x23e\x23f\x240\x241\x242\x243\x244\x245\x246\x247\x248\x249\x24a\x24b\x24c\x24d\x24e\x24f\x250\x251\x252\x253\x254\x255\x256\x257\x258\x259\x25a\x25b\x25c\x25d\x25e\x25f\x260\x261\x262\x263\x264\x265\x266\x267\x268\x269\x26a\x26b\x26c\x26d\x26e\x26f\x270\x271\x272\x273\x274\x275\x276\x277\x278\x279\x27a\x27b\x27c\x27d\x27e\x27f\x280\x281\x282\x283\x284\x285\x286\x287\x288\x289\x28a\x28b\x28c\x28d\x28e\x28f\x290\x291\x292\x293\x294\x295\x296\x297\x298\x299\x29a\x29b\x29c\x29d\x29e\x29f\x2a0\x2a1\x2a2\x2a3\x2a4\x2a5\x2a6\x2a7\x2a8\x2a9\x2aa\x2ab\x2ac\x2ad\x2ae\x2af\x2b0\x2b1\x2b2\x2b3\x2b4\x2b5\x2b6\x2b7\x2b8\x2b9");
	$txtfield->addString($str);
	return $txtfield;
}

$txtv = maketextfield("txtv",$fuente,"Nico");
$txt=$movie->add($txtv);
$txt->moveTo(-2400,-2800);
#$txt->moveTo(10,10);
$txt->setName("textoload");

#################################################
## SELECT TIMEOUT BOX
#################################################
$dropbox1 = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(5600, 0);
$s1->setRightFill(0xf2, 0xf2, 0xf6);
#$s1->setLine(1, 0xbe, 0xbe, 0xbe);
$s1->drawLine(0, 3400);
$s1->drawLine(-5600, 0);
#$s1->setLine(1, 0xf2, 0xf2, 0xf6);
$s1->drawLine(0, -3400);
$s1->drawLine(5600, 0);

### MovieClip 2 ###
$s2 = new SWF::MovieClip();  # 1 frames
$s2->add($s1);
$s2->nextFrame();  # end of clip frame 1 

$i1 = $dropbox1->add($s2);
#$i1->scaleTo(0.714279, 0.105881);
$i1->scaleTo(0.328, 0.0635);
$i1->setName('bg');

### Shape 3 ###
# Scroll DOWN #
$s3 = new SWF::Shape();
$s3->movePenTo(320, 0);
$s3->setRightFill(0x6f, 0x7f, 0x7f);
$s3->setLine(1, 0xbe, 0xbe, 0xbe);
$s3->drawLine(0, 340);
$s3->drawLine(-320, 0);
$s3->setLine(0,0,0,0);
$s3->drawLine(0, -340);
$s3->drawLine(320, 0);
$s3->setLeftFill();
$s3->setRightFill();
$s3->setLine(0,0,0,0);
$s3->movePenTo(160, 220);
$s3->setRightFill(0x7f, 0x7f, 0x7f);
$s3->drawLine(-80, -80);
$s3->drawLine(160, 0);
$s3->drawLine(-80, 80);
### MovieClip 4 ###
$s4 = new SWF::MovieClip();  # 1 frames
$s4->add($s3);

$s4->add(new SWF::Action("
this.onPress = function() {
	if(_parent._currentframe == 1) {
		_root.despliega_select();
	} else {
		var incremento = 1;
		var cuantashay = _global.opcionesTimeout.length;
		cuantashay = cuantashay - 4;
		if (_global.positionselect < cuantashay) {
			for(a=0;a<5;a++) {
				var indice = a + _global.positionselect;
				if (_global.opcionesTimeout[indice] != undefined) {
					_root['option'+a].legend = _global.opcionesTimeout[indice];
					incremento = 1;
				} else {
					incremento = 0;
				}
			}
		_global.positionselect=_global.positionselect+incremento;
		}
	}
};

"));
$s4->nextFrame();  # end of clip frame 1 
### END SCROLL DOWN #####


#### SCROLL UP ######
### Shape 5 ###
$s5 = new SWF::Shape();
$s5->movePenTo(320, 0);
$s5->setRightFill(0x6f, 0x7f, 0x7f);
$s5->setLine(1, 0xbe, 0xbe, 0xbe);
$s5->drawLine(0, 340);
$s5->drawLine(-320, 0);
$s5->setLine(0,0,0,0);
$s5->drawLine(0, -340);
$s5->drawLine(320, 0);
$s5->setLeftFill();
$s5->setRightFill();
$s5->setLine(0,0,0,0);
$s5->movePenTo(160, 140);
$s5->setRightFill(0x7f, 0x7f, 0x7f);
$s5->drawLine(-80, 80);
$s5->drawLine(160, 0);
$s5->drawLine(-80, -80);
### MovieClip 6 ###
$s6 = new SWF::MovieClip();  # 1 frames
$s6->add($s5);
$s6->add(new SWF::Action("
this.onPress = function() {
        var incremento = 1;
        if (_global.positionselect > 0) {
            for(a=0;a<5;a++) {
                var indice = a + _global.positionselect - 1;
                if (_global.opcionesTimeout[indice] != undefined) {
                    _root['option'+a].legend = _global.opcionesTimeout[indice];
                    incremento = -1;
                } else {
                    incremento = 0;
                }
            }
        _global.positionselect=_global.positionselect+incremento;
        }
};

"));
$s6->nextFrame();  # end of clip frame 1
##### END SCROLL UP ######

$i3 = $dropbox1->add($s4);
$i3->moveTo(1640, 10);
$i3->scaleTo(0.6, 0.6);
$i3->setName('ScrollDown');

$dropbox1->nextFrame();  # end of frame 1
$dropbox1->remove($i3);

$i1 = $dropbox1->add($s2);
$i1->scaleTo(0.328, 0.4);
$i1->setName('bg');
$i3 = $dropbox1->add($s4);
$i3->moveTo(1640,1160);
$i3->scaleTo(0.6, 0.6);
$i3->setName('ScrollDown');


$i3 = $dropbox1->add($s6);
$i3->moveTo(1640, 10);
$i3->scaleTo(0.6, 0.6);
$i3->setName('ScrollUp');

$option = new SWF::MovieClip();
$i3 = $option->add($s2);
$i3->scaleTo(0.29, 0.0635);
$i3->setName('bg');

$font_general = new SWF::Font($fuente);  

$s8 = new SWF::TextField(SWFTEXTFIELD_NOEDIT | SWFTEXTFIELD_USEFONT | SWFTEXTFIELD_NOSELECT );
$s8->setBounds(1411, 398);
$s8->setFont($font_general);
#$s8->setFont(new SWF::Font("_sans"));
$s8->setHeight(180);
$s8->setColor(0x00, 0x00, 0x00, 0xff);
$s8->align(SWFTEXTFIELD_ALIGN_LEFT);
$s8->setName('legend');
$s8->addString('Select Timeout');

$i3 = $option->add($s8);
$i3->moveTo(70,10);

$option->nextFrame();
$dropbox1->nextFrame();  # end of frame 2
##################################################
# END SELECT TIMEOUT BOX
##################################################

## MovieCLIP progress graphic
##
$progressclip = new SWF::Sprite();

### Shape 2 ###
$s2 = new SWF::Shape();
$s2->movePenTo(140, -53);
$s2->setLeftFill(0xff, 0xff, 0xff, 0x66);
$s2->setRightFill();
#$s2->setLine(0,0,0,0);
$s2->drawLine(140, -62);
$s2->drawLine(-40, -70);
$s2->drawLine(-124, 91);
$s2->drawCurve(16, 19, 8, 22);
$s2->movePenTo(95, -115);
$s2->setLeftFill(0xff, 0xff, 0xff, 0x5a);
$s2->setRightFill();
#$s2->setLine(0,255,255,255);
$s2->drawLine(90, -125);
$s2->drawLine(-70, -40);
$s2->drawLine(-62, 140);
$s2->drawLine(42, 25);
$s2->movePenTo(40, -300);
$s2->setLeftFill(0xff, 0xff, 0xff);
$s2->setRightFill();
#$s2->setLine(0,255,255,255);
$s2->drawLine(-80, 0);
$s2->drawLine(16, 152);
$s2->drawLine(23, -1);
$s2->drawLine(26, 2);
$s2->drawLine(15, -153);
$s2->movePenTo(148, -24);
$s2->setLeftFill(0xff, 0xff, 0xff, 0x73);
$s2->setRightFill();
#$s2->setLine(0,255,255,255);
$s2->drawLine(2, 24);
$s2->drawLine(-2, 25);
$s2->drawLine(152, 15);
$s2->drawLine(0, -80);
$s2->drawLine(-152, 16);
$s2->movePenTo(280, 115);
$s2->setLeftFill(0xff, 0xff, 0xff, 0x80);
$s2->setRightFill();
#$s2->setLine(0,0,0,0);
$s2->drawLine(-140, -62);
$s2->drawCurve(-8, 22, -16, 20);
$s2->drawLine(124, 90);
$s2->drawLine(40, -70);
$s2->movePenTo(53, 141);
$s2->setLeftFill(0xff, 0xff, 0xff, 0x8d);
$s2->setRightFill();
#$s2->setLine(0,0,0,0);
$s2->drawLine(62, 139);
$s2->drawLine(70, -40);
$s2->drawLine(-90, -124);
$s2->drawLine(-42, 25);
$s2->movePenTo(25, 149);
$s2->setLeftFill(0xff, 0xff, 0xff, 0x99);
$s2->setRightFill();
#$s2->setLine(0,0,0,0);
$s2->drawLine(-26, 2);
$s2->drawLine(-23, -2);
$s2->drawLine(-16, 151);
$s2->drawLine(80, 0);
$s2->drawLine(-15, -151);
$s2->movePenTo(-239, -185);
$s2->setLeftFill();
$s2->setRightFill(0xff, 0xff, 0xff, 0xcc);
#$s2->setLine(0,0,0,0);
$s2->drawLine(123, 90);
$s2->drawCurve(-17, 19, -8, 23);
$s2->drawLine(-138, -62);
$s2->drawLine(40, -70);
$s2->movePenTo(-53, -140);
$s2->setLeftFill(0xff, 0xff, 0xff, 0xe6);
$s2->setRightFill();
#$s2->setLine(0,0,0,0);
$s2->drawLine(-62, -140);
$s2->drawLine(-70, 40);
$s2->drawLine(90, 124);
$s2->drawCurve(19, -16, 23, -8);
$s2->movePenTo(-148, -24);
$s2->setLeftFill();
$s2->setRightFill(0xff, 0xff, 0xff, 0xc0);
#$s2->setLine(0,0,0,0);
$s2->drawLine(-2, 24);
$s2->drawLine(2, 25);
$s2->drawLine(-152, 15);
$s2->drawLine(0, -80);
$s2->drawLine(152, 16);
$s2->movePenTo(-141, 53);
$s2->setLeftFill(0xff, 0xff, 0xff, 0xb3);
$s2->setRightFill();
#$s2->setLine(0,0,0,0);
$s2->drawLine(-138, 62);
$s2->drawLine(40, 70);
$s2->drawLine(123, -89);
$s2->drawLine(-25, -43);
$s2->movePenTo(-95, 117);
$s2->setLeftFill(0xff, 0xff, 0xff, 0xa6);
$s2->setRightFill();
#$s2->setLine(0,0,0,0);
$s2->drawLine(-90, 123);
$s2->drawLine(70, 40);
$s2->drawLine(62, -138);
$s2->drawCurve(-23, -9, -19, -16);

### Shape 3 ###
$s3 = new SWF::Shape();
$s3->movePenTo(116, -94);
$s3->setLeftFill(0xff, 0xff, 0xff, 0x5a);
$s3->setRightFill();
#$s3->setLine(0,0,0,0);
$s3->drawCurve(16, 19, 8, 22);
$s3->drawLine(140, -62);
$s3->drawLine(-40, -70);
$s3->drawLine(-124, 90);
$s3->drawLine(0, 1);
$s3->movePenTo(185, -240);
$s3->setLeftFill(0xff, 0xff, 0xff);
$s3->setRightFill();
#$s3->setLine(0,0,0,0);
$s3->drawLine(-69, -40);
$s3->drawLine(-63, 140);
$s3->drawLine(42, 25);
$s3->drawLine(90, -125);
$s3->movePenTo(40, -300);
$s3->setLeftFill(0xff, 0xff, 0xff, 0xe6);
$s3->setRightFill();
#$s3->setLine(0,0,0,0);
$s3->drawLine(-80, 0);
$s3->drawLine(16, 152);
$s3->drawLine(23, -1);
$s3->drawLine(25, 2);
$s3->drawLine(16, -153);
$s3->movePenTo(148, -24);
$s3->setLeftFill(0xff, 0xff, 0xff, 0x66);
$s3->setRightFill();
#$s3->setLine(0,0,0,0);
$s3->drawLine(2, 24);
$s3->drawLine(-2, 24);
$s3->drawLine(152, 16);
$s3->drawLine(1, -80);
$s3->drawLine(-153, 16);
$s3->movePenTo(280, 116);
$s3->setLeftFill(0xff, 0xff, 0xff, 0x73);
$s3->setRightFill();
#$s3->setLine(0,0,0,0);
$s3->drawLine(-140, -63);
$s3->drawCurve(-8, 22, -16, 20);
$s3->drawLine(124, 90);
$s3->drawLine(40, -69);
$s3->movePenTo(95, 116);
$s3->setLeftFill(0xff, 0xff, 0xff, 0x80);
$s3->setRightFill();
#$s3->setLine(0,0,0,0);
$s3->drawLine(-41, 25);
$s3->drawLine(62, 139);
$s3->drawLine(69, -40);
$s3->drawLine(-90, -124);
$s3->movePenTo(40, 300);
$s3->setLeftFill();
$s3->setRightFill(0xff, 0xff, 0xff, 0x8d);
#$s3->setLine(0,0,0,0);
$s3->drawLine(-80, 0);
$s3->drawLine(16, -151);
$s3->drawLine(23, 2);
$s3->drawLine(25, -2);
$s3->drawLine(16, 151);
$s3->movePenTo(-240, -184);
$s3->setLeftFill();
$s3->setRightFill(0xff, 0xff, 0xff, 0xc0);
#$s3->setLine(0,0,0,0);
$s3->drawLine(124, 89);
$s3->drawCurve(-17, 19, -8, 23);
$s3->drawLine(-139, -62);
$s3->drawLine(40, -69);
$s3->movePenTo(-52, -140);
$s3->setLeftFill(0xff, 0xff, 0xff, 0xcc);
$s3->setRightFill();
#$s3->setLine(0,0,0,0);
$s3->drawLine(-63, -140);
$s3->drawLine(-69, 40);
$s3->drawLine(89, 124);
$s3->drawCurve(19, -16, 23, -8);
$s3->drawLine(1, 0);
$s3->movePenTo(-148, -24);
$s3->setLeftFill();
$s3->setRightFill(0xff, 0xff, 0xff, 0xb3);
#$s3->setLine(0,0,0,0);
$s3->drawLine(-2, 24);
$s3->drawLine(2, 24);
$s3->drawLine(-151, 16);
$s3->drawLine(0, -80);
$s3->drawLine(151, 16);
$s3->movePenTo(-141, 53);
$s3->setLeftFill(0xff, 0xff, 0xff, 0xa6);
$s3->setRightFill();
#$s3->setLine(0,0,0,0);
$s3->drawLine(-139, 62);
$s3->drawLine(40, 70);
$s3->drawLine(124, -89);
$s3->drawLine(-25, -43);
$s3->movePenTo(-95, 117);
$s3->setLeftFill(0xff, 0xff, 0xff, 0x99);
$s3->setRightFill();
#$s3->setLine(0,0,0,0);
$s3->drawLine(-89, 123);
$s3->drawLine(69, 40);
$s3->drawLine(62, -138);
$s3->drawCurve(-23, -9, -19, -16);

### Shape 4 ###
$s4 = new SWF::Shape();
$s4->movePenTo(25, -147);
$s4->setLeftFill(0xff, 0xff, 0xff, 0xcc);
$s4->setRightFill();
#$s4->setLine(0,0,0,0);
$s4->drawLine(16, -153);
$s4->drawLine(-81, 0);
$s4->drawLine(16, 152);
$s4->drawLine(23, -1);
$s4->drawLine(25, 2);
$s4->drawLine(1, 0);
$s4->movePenTo(116, -94);
$s4->setLeftFill(0xff, 0xff, 0xff);
$s4->setRightFill();
#$s4->setLine(0,0,0,0);
$s4->drawCurve(16, 19, 8, 22);
$s4->drawLine(0, 1);
$s4->drawLine(140, -63);
$s4->drawLine(-40, -69);
$s4->drawLine(-124, 89);
$s4->drawLine(0, 1);
$s4->movePenTo(185, -240);
$s4->setLeftFill(0xff, 0xff, 0xff, 0xe6);
$s4->setRightFill();
#$s4->setLine(0,0,0,0);
$s4->drawLine(-69, -40);
$s4->drawLine(-63, 140);
$s4->drawLine(41, 25);
$s4->drawLine(91, -125);
$s4->movePenTo(148, -24);
$s4->setLeftFill(0xff, 0xff, 0xff, 0x5a);
$s4->setRightFill();
#$s4->setLine(0,0,0,0);
$s4->drawLine(2, 24);
$s4->drawLine(-2, 24);
$s4->drawLine(152, 16);
$s4->drawLine(0, -80);
$s4->drawLine(-152, 16);
$s4->movePenTo(280, 116);
$s4->setLeftFill(0xff, 0xff, 0xff, 0x66);
$s4->setRightFill();
#$s4->setLine(0,0,0,0);
$s4->drawLine(-140, -63);
$s4->drawCurve(-8, 22, -16, 20);
$s4->drawLine(124, 90);
$s4->drawLine(40, -69);
$s4->movePenTo(95, 116);
$s4->setLeftFill(0xff, 0xff, 0xff, 0x73);
$s4->setRightFill();
#$s4->setLine(0,0,0,0);
$s4->drawLine(-41, 25);
$s4->drawLine(-1, 0);
$s4->drawLine(63, 139);
$s4->drawLine(69, -40);
$s4->drawLine(-90, -124);
$s4->movePenTo(24, 149);
$s4->setLeftFill(0xff, 0xff, 0xff, 0x80);
$s4->setRightFill();
#$s4->setLine(0,0,0,0);
$s4->drawLine(-25, 2);
$s4->drawLine(-23, -2);
$s4->drawLine(-16, 151);
$s4->drawLine(81, 0);
$s4->drawLine(-16, -151);
$s4->drawLine(-1, 0);
$s4->movePenTo(-239, -184);
$s4->setLeftFill();
$s4->setRightFill(0xff, 0xff, 0xff, 0xb3);
#$s4->setLine(0,0,0,0);
$s4->drawLine(123, 89);
$s4->drawCurve(-17, 19, -8, 23);
$s4->drawLine(-138, -62);
$s4->drawLine(40, -69);
$s4->movePenTo(-53, -140);
$s4->setLeftFill(0xff, 0xff, 0xff, 0xc0);
$s4->setRightFill();
#$s4->setLine(0,0,0,0);
$s4->drawLine(-62, -140);
$s4->drawLine(-69, 40);
$s4->drawLine(89, 124);
$s4->drawCurve(19, -16, 23, -8);
$s4->movePenTo(-148, -24);
$s4->setLeftFill();
$s4->setRightFill(0xff, 0xff, 0xff, 0xa6);
#$s4->setLine(0,0,0,0);
$s4->drawLine(-2, 24);
$s4->drawLine(2, 24);
$s4->drawLine(0, 1);
$s4->drawLine(-152, 15);
$s4->drawLine(0, -80);
$s4->drawLine(152, 16);
$s4->movePenTo(-141, 53);
$s4->setLeftFill(0xff, 0xff, 0xff, 0x99);
$s4->setRightFill();
#$s4->setLine(0,0,0,0);
$s4->drawLine(-139, 63);
$s4->drawLine(40, 69);
$s4->drawLine(124, -89);
$s4->drawLine(-25, -43);
$s4->movePenTo(-95, 117);
$s4->setLeftFill(0xff, 0xff, 0xff, 0x8d);
$s4->setRightFill();
#$s4->setLine(0,0,0,0);
$s4->drawLine(-89, 123);
$s4->drawLine(69, 40);
$s4->drawLine(62, -138);
$s4->drawCurve(-23, -9, -19, -16);

### Shape 5 ###
$s5 = new SWF::Shape();
$s5->movePenTo(25, -147);
$s5->setLeftFill(0xff, 0xff, 0xff, 0xc0);
$s5->setRightFill();
#$s5->setLine(0,0,0,0);
$s5->drawLine(15, -153);
$s5->drawLine(-80, 0);
$s5->drawLine(16, 152);
$s5->drawLine(23, -1);
$s5->drawLine(25, 2);
$s5->drawLine(1, 0);
$s5->movePenTo(95, -115);
$s5->setLeftFill(0xff, 0xff, 0xff, 0xcc);
$s5->setRightFill();
#$s5->setLine(0,0,0,0);
$s5->drawLine(90, -124);
$s5->drawLine(-69, -41);
$s5->drawLine(-63, 140);
$s5->drawLine(41, 25);
$s5->drawLine(1, 0);
$s5->movePenTo(140, -53);
$s5->setLeftFill(0xff, 0xff, 0xff, 0xe6);
$s5->setRightFill();
#$s5->setLine(0,0,0,0);
$s5->drawLine(140, -62);
$s5->drawLine(-40, -69);
$s5->drawLine(-124, 89);
$s5->drawLine(0, 1);
$s5->drawLine(24, 41);
$s5->movePenTo(148, -24);
$s5->setLeftFill(0xff, 0xff, 0xff);
$s5->setRightFill();
#$s5->setLine(0,0,0,0);
$s5->drawLine(2, 24);
$s5->drawLine(-2, 24);
$s5->drawLine(0, 1);
$s5->drawLine(152, 15);
$s5->drawLine(0, -80);
$s5->drawLine(-152, 16);
$s5->movePenTo(280, 115);
$s5->setLeftFill(0xff, 0xff, 0xff, 0x5a);
$s5->setRightFill();
#$s5->setLine(0,0,0,0);
$s5->drawLine(-140, -62);
$s5->drawCurve(-8, 22, -16, 20);
$s5->drawLine(124, 90);
$s5->drawLine(40, -70);
$s5->movePenTo(95, 116);
$s5->setLeftFill(0xff, 0xff, 0xff, 0x66);
$s5->setRightFill();
#$s5->setLine(0,0,0,0);
$s5->drawLine(-41, 25);
$s5->drawLine(62, 139);
$s5->drawLine(69, -40);
$s5->drawLine(-90, -124);
$s5->movePenTo(24, 149);
$s5->setLeftFill(0xff, 0xff, 0xff, 0x73);
$s5->setRightFill();
#$s5->setLine(0,0,0,0);
$s5->drawLine(-25, 2);
$s5->drawLine(-23, -2);
$s5->drawLine(-16, 151);
$s5->drawLine(80, 0);
$s5->drawLine(-15, -151);
$s5->drawLine(-1, 0);
$s5->movePenTo(-239, -185);
$s5->setLeftFill();
$s5->setRightFill(0xff, 0xff, 0xff, 0xa6);
#$s5->setLine(0,0,0,0);
$s5->drawLine(123, 90);
$s5->drawCurve(-17, 19, -8, 23);
$s5->drawLine(-139, -62);
$s5->drawLine(41, -70);
$s5->movePenTo(-52, -140);
$s5->setLeftFill(0xff, 0xff, 0xff, 0xb3);
$s5->setRightFill();
#$s5->setLine(0,0,0,0);
$s5->drawLine(-63, -139);
$s5->drawLine(-69, 40);
$s5->drawLine(89, 123);
$s5->drawCurve(19, -16, 23, -8);
$s5->drawLine(1, 0);
$s5->movePenTo(-148, -24);
$s5->setLeftFill();
$s5->setRightFill(0xff, 0xff, 0xff, 0x99);
#$s5->setLine(0,0,0,0);
$s5->drawLine(-2, 24);
$s5->drawLine(2, 24);
$s5->drawLine(0, 1);
$s5->drawLine(-152, 15);
$s5->drawLine(0, -80);
$s5->drawLine(152, 16);
$s5->movePenTo(-141, 53);
$s5->setLeftFill(0xff, 0xff, 0xff, 0x8d);
$s5->setRightFill();
#$s5->setLine(0,0,0,0);
$s5->drawLine(-138, 62);
$s5->drawLine(40, 70);
$s5->drawLine(123, -89);
$s5->drawLine(-25, -43);
$s5->movePenTo(-53, 142);
$s5->setLeftFill(0xff, 0xff, 0xff, 0x80);
$s5->setRightFill();
#$s5->setLine(0,0,0,0);
$s5->drawCurve(-23, -9, -19, -16);
$s5->drawLine(-89, 123);
$s5->drawLine(69, 40);
$s5->drawLine(62, -138);

### Shape 6 ###
$s6 = new SWF::Shape();
$s6->movePenTo(25, -147);
$s6->setLeftFill(0xff, 0xff, 0xff, 0xb3);
$s6->setRightFill();
#$s6->setLine(0,0,0,0);
$s6->drawLine(15, -152);
$s6->drawLine(-80, 0);
$s6->drawLine(16, 151);
$s6->drawLine(23, -1);
$s6->drawLine(25, 2);
$s6->drawLine(1, 0);
$s6->movePenTo(95, -115);
$s6->setLeftFill(0xff, 0xff, 0xff, 0xc0);
$s6->setRightFill();
#$s6->setLine(0,0,0,0);
$s6->drawLine(90, -125);
$s6->drawLine(-69, -40);
$s6->drawLine(-63, 140);
$s6->drawLine(41, 25);
$s6->drawLine(1, 0);
$s6->movePenTo(140, -52);
$s6->setLeftFill(0xff, 0xff, 0xff, 0xcc);
$s6->setRightFill();
#$s6->setLine(0,0,0,0);
$s6->drawLine(140, -63);
$s6->drawLine(-40, -69);
$s6->drawLine(-124, 89);
$s6->drawLine(0, 1);
$s6->drawLine(24, 41);
$s6->drawLine(0, 1);
$s6->movePenTo(148, -24);
$s6->setLeftFill(0xff, 0xff, 0xff, 0xe6);
$s6->setRightFill();
#$s6->setLine(0,0,0,0);
$s6->drawLine(2, 24);
$s6->drawLine(-2, 24);
$s6->drawLine(152, 16);
$s6->drawLine(0, -80);
$s6->drawLine(-152, 16);
$s6->movePenTo(280, 116);
$s6->setLeftFill(0xff, 0xff, 0xff);
$s6->setRightFill();
#$s6->setLine(0,0,0,0);
$s6->drawLine(-140, -63);
$s6->drawCurve(-8, 22, -16, 20);
$s6->drawLine(124, 90);
$s6->drawLine(40, -69);
$s6->movePenTo(95, 116);
$s6->setLeftFill(0xff, 0xff, 0xff, 0x5a);
$s6->setRightFill();
#$s6->setLine(0,0,0,0);
$s6->drawLine(-41, 25);
$s6->drawLine(62, 139);
$s6->drawLine(69, -40);
$s6->drawLine(-90, -124);
$s6->movePenTo(24, 149);
$s6->setLeftFill(0xff, 0xff, 0xff, 0x66);
$s6->setRightFill();
#$s6->setLine(0,0,0,0);
$s6->drawLine(-25, 2);
$s6->drawLine(-23, -2);
$s6->drawLine(-16, 151);
$s6->drawLine(80, 0);
$s6->drawLine(-15, -151);
$s6->drawLine(-1, 0);
$s6->movePenTo(-240, -184);
$s6->setLeftFill();
$s6->setRightFill(0xff, 0xff, 0xff, 0x99);
#$s6->setLine(0,0,0,0);
$s6->drawLine(124, 89);
$s6->drawCurve(-17, 19, -8, 23);
$s6->drawLine(-139, -62);
$s6->drawLine(40, -69);
$s6->movePenTo(-52, -140);
$s6->setLeftFill(0xff, 0xff, 0xff, 0xa6);
$s6->setRightFill();
#$s6->setLine(0,0,0,0);
$s6->drawLine(-63, -140);
$s6->drawLine(-69, 40);
$s6->drawLine(89, 124);
$s6->drawCurve(19, -16, 23, -8);
$s6->drawLine(1, 0);
$s6->movePenTo(-148, -24);
$s6->setLeftFill();
$s6->setRightFill(0xff, 0xff, 0xff, 0x8d);
#$s6->setLine(0,0,0,0);
$s6->drawLine(-2, 24);
$s6->drawLine(2, 24);
$s6->drawLine(-151, 16);
$s6->drawLine(0, -80);
$s6->drawLine(151, 16);
$s6->movePenTo(-95, 117);
$s6->setLeftFill(0xff, 0xff, 0xff, 0x73);
$s6->setRightFill();
#$s6->setLine(0,0,0,0);
$s6->drawLine(-89, 123);
$s6->drawLine(69, 40);
$s6->drawLine(62, -138);
$s6->drawCurve(-23, -9, -19, -16);
$s6->movePenTo(-141, 54);
$s6->setLeftFill(0xff, 0xff, 0xff, 0x80);
$s6->setRightFill();
#$s6->setLine(0,0,0,0);
$s6->drawLine(-139, 62);
$s6->drawLine(41, 69);
$s6->drawLine(123, -89);
$s6->drawLine(-25, -42);

### Shape 7 ###
$s7 = new SWF::Shape();
$s7->movePenTo(25, -147);
$s7->setLeftFill(0xff, 0xff, 0xff, 0xa6);
$s7->setRightFill();
#$s7->setLine(0,0,0,0);
$s7->drawLine(16, -153);
$s7->drawLine(-81, 0);
$s7->drawLine(16, 152);
$s7->drawLine(23, -1);
$s7->drawLine(25, 2);
$s7->drawLine(1, 0);
$s7->movePenTo(95, -115);
$s7->setLeftFill(0xff, 0xff, 0xff, 0xb3);
$s7->setRightFill();
#$s7->setLine(0,0,0,0);
$s7->drawLine(90, -124);
$s7->drawLine(-70, -40);
$s7->drawLine(-62, 139);
$s7->drawLine(41, 25);
$s7->drawLine(1, 0);
$s7->movePenTo(140, -52);
$s7->setLeftFill(0xff, 0xff, 0xff, 0xc0);
$s7->setRightFill();
#$s7->setLine(0,0,0,0);
$s7->drawLine(140, -63);
$s7->drawLine(-40, -69);
$s7->drawLine(-124, 90);
$s7->drawLine(24, 41);
$s7->drawLine(0, 1);
$s7->movePenTo(300, -40);
$s7->setLeftFill();
$s7->setRightFill(0xff, 0xff, 0xff, 0xcc);
#$s7->setLine(0,0,0,0);
$s7->drawLine(0, 81);
$s7->drawLine(-152, -16);
$s7->drawLine(0, -1);
$s7->drawLine(2, -24);
$s7->drawLine(-2, -24);
$s7->drawLine(152, -16);
$s7->movePenTo(280, 116);
$s7->setLeftFill(0xff, 0xff, 0xff, 0xe6);
$s7->setRightFill();
#$s7->setLine(0,0,0,0);
$s7->drawLine(-140, -63);
$s7->drawCurve(-8, 22, -16, 20);
$s7->drawLine(124, 90);
$s7->drawLine(40, -69);
$s7->movePenTo(95, 116);
$s7->setLeftFill(0xff, 0xff, 0xff);
$s7->setRightFill();
#$s7->setLine(0,0,0,0);
$s7->drawLine(-41, 25);
$s7->drawLine(-1, 0);
$s7->drawLine(63, 139);
$s7->drawLine(69, -40);
$s7->drawLine(-90, -124);
$s7->movePenTo(24, 149);
$s7->setLeftFill(0xff, 0xff, 0xff, 0x5a);
$s7->setRightFill();
#$s7->setLine(0,0,0,0);
$s7->drawLine(-25, 2);
$s7->drawLine(-23, -2);
$s7->drawLine(-16, 151);
$s7->drawLine(81, 0);
$s7->drawLine(-16, -151);
$s7->drawLine(-1, 0);
$s7->movePenTo(-239, -184);
$s7->setLeftFill();
$s7->setRightFill(0xff, 0xff, 0xff, 0x8d);
#$s7->setLine(0,0,0,0);
$s7->drawLine(123, 89);
$s7->drawCurve(-17, 19, -8, 23);
$s7->drawLine(-138, -62);
$s7->drawLine(40, -69);
$s7->movePenTo(-53, -140);
$s7->setLeftFill(0xff, 0xff, 0xff, 0x99);
$s7->setRightFill();
#$s7->setLine(0,0,0,0);
$s7->drawLine(-62, -140);
$s7->drawLine(-69, 40);
$s7->drawLine(89, 124);
$s7->drawCurve(19, -16, 23, -8);
$s7->movePenTo(-300, -40);
$s7->setLeftFill(0xff, 0xff, 0xff, 0x80);
$s7->setRightFill();
#$s7->setLine(0,0,0,0);
$s7->drawLine(0, 81);
$s7->drawLine(152, -16);
$s7->drawLine(0, -1);
$s7->drawLine(-2, -24);
$s7->drawLine(2, -24);
$s7->drawLine(-152, -16);
$s7->movePenTo(-141, 53);
$s7->setLeftFill(0xff, 0xff, 0xff, 0x73);
$s7->setRightFill();
#$s7->setLine(0,0,0,0);
$s7->drawLine(-139, 63);
$s7->drawLine(40, 69);
$s7->drawLine(124, -89);
$s7->drawLine(-25, -43);
$s7->movePenTo(-53, 142);
$s7->setLeftFill(0xff, 0xff, 0xff, 0x66);
$s7->setRightFill();
#$s7->setLine(0,0,0,0);
$s7->drawCurve(-23, -9, -19, -16);
$s7->drawLine(-89, 123);
$s7->drawLine(69, 40);
$s7->drawLine(62, -138);

### Shape 8 ###
$s8 = new SWF::Shape();
$s8->movePenTo(25, -147);
$s8->setLeftFill(0xff, 0xff, 0xff, 0x99);
$s8->setRightFill();
#$s8->setLine(0,0,0,0);
$s8->drawLine(15, -153);
$s8->drawLine(-80, 0);
$s8->drawLine(16, 152);
$s8->drawLine(23, -1);
$s8->drawLine(25, 2);
$s8->drawLine(1, 0);
$s8->movePenTo(95, -115);
$s8->setLeftFill(0xff, 0xff, 0xff, 0xa6);
$s8->setRightFill();
#$s8->setLine(0,0,0,0);
$s8->drawLine(90, -124);
$s8->drawLine(-69, -41);
$s8->drawLine(-63, 140);
$s8->drawLine(41, 25);
$s8->drawLine(1, 0);
$s8->movePenTo(140, -52);
$s8->setLeftFill(0xff, 0xff, 0xff, 0xb3);
$s8->setRightFill();
#$s8->setLine(0,0,0,0);
$s8->drawLine(140, -63);
$s8->drawLine(-40, -69);
$s8->drawLine(-124, 90);
$s8->drawLine(24, 41);
$s8->drawLine(0, 1);
$s8->movePenTo(300, -40);
$s8->setLeftFill();
$s8->setRightFill(0xff, 0xff, 0xff, 0xc0);
#$s8->setLine(0,0,0,0);
$s8->drawLine(0, 80);
$s8->drawLine(-152, -15);
$s8->drawLine(0, -1);
$s8->drawLine(2, -24);
$s8->drawLine(-2, -24);
$s8->drawLine(152, -16);
$s8->movePenTo(240, 185);
$s8->setLeftFill();
$s8->setRightFill(0xff, 0xff, 0xff, 0xcc);
#$s8->setLine(0,0,0,0);
$s8->drawLine(-124, -90);
$s8->drawCurve(16, -20, 8, -22);
$s8->drawLine(140, 63);
$s8->drawLine(-40, 69);
$s8->movePenTo(95, 116);
$s8->setLeftFill(0xff, 0xff, 0xff, 0xe6);
$s8->setRightFill();
#$s8->setLine(0,0,0,0);
$s8->drawLine(-41, 25);
$s8->drawLine(62, 139);
$s8->drawLine(69, -40);
$s8->drawLine(-90, -124);
$s8->movePenTo(24, 149);
$s8->setLeftFill(0xff, 0xff, 0xff);
$s8->setRightFill();
#$s8->setLine(0,0,0,0);
$s8->drawLine(-25, 2);
$s8->drawLine(-23, -2);
$s8->drawLine(-16, 151);
$s8->drawLine(80, 0);
$s8->drawLine(-15, -151);
$s8->drawLine(-1, 0);
$s8->movePenTo(-280, -115);
$s8->setLeftFill(0xff, 0xff, 0xff, 0x80);
$s8->setRightFill();
#$s8->setLine(0,0,0,0);
$s8->drawLine(139, 62);
$s8->drawCurve(8, -23, 17, -19);
$s8->drawLine(-124, -89);
$s8->drawLine(-40, 69);
$s8->movePenTo(-52, -140);
$s8->setLeftFill(0xff, 0xff, 0xff, 0x8d);
$s8->setRightFill();
#$s8->setLine(0,0,0,0);
$s8->drawLine(-63, -139);
$s8->drawLine(-69, 40);
$s8->drawLine(89, 123);
$s8->drawCurve(19, -16, 23, -8);
$s8->drawLine(1, 0);
$s8->movePenTo(-300, -40);
$s8->setLeftFill(0xff, 0xff, 0xff, 0x73);
$s8->setRightFill();
#$s8->setLine(0,0,0,0);
$s8->drawLine(0, 80);
$s8->drawLine(152, -15);
$s8->drawLine(0, -1);
$s8->drawLine(-2, -24);
$s8->drawLine(2, -24);
$s8->drawLine(-152, -16);
$s8->movePenTo(-140, 54);
$s8->setLeftFill(0xff, 0xff, 0xff, 0x66);
$s8->setRightFill();
#$s8->setLine(0,0,0,0);
$s8->drawLine(-140, 62);
$s8->drawLine(40, 69);
$s8->drawLine(124, -89);
$s8->drawCurve(-17, -20, -7, -22);
$s8->movePenTo(-53, 142);
$s8->setLeftFill(0xff, 0xff, 0xff, 0x5a);
$s8->setRightFill();
#$s8->setLine(0,0,0,0);
$s8->drawCurve(-23, -9, -19, -16);
$s8->drawLine(-89, 123);
$s8->drawLine(69, 40);
$s8->drawLine(62, -138);

### Shape 9 ###
$s9 = new SWF::Shape();
$s9->movePenTo(25, -147);
$s9->setLeftFill(0xff, 0xff, 0xff, 0x8d);
$s9->setRightFill();
#$s9->setLine(0,0,0,0);
$s9->drawLine(15, -152);
$s9->drawLine(-80, 0);
$s9->drawLine(16, 151);
$s9->drawLine(23, -1);
$s9->drawLine(25, 2);
$s9->drawLine(1, 0);
$s9->movePenTo(95, -115);
$s9->setLeftFill(0xff, 0xff, 0xff, 0x99);
$s9->setRightFill();
#$s9->setLine(0,0,0,0);
$s9->drawLine(90, -125);
$s9->drawLine(-69, -40);
$s9->drawLine(-63, 140);
$s9->drawLine(41, 25);
$s9->drawLine(1, 0);
$s9->movePenTo(140, -52);
$s9->setLeftFill(0xff, 0xff, 0xff, 0xa6);
$s9->setRightFill();
#$s9->setLine(0,0,0,0);
$s9->drawLine(140, -63);
$s9->drawLine(-40, -69);
$s9->drawLine(-124, 89);
$s9->drawLine(0, 1);
$s9->drawLine(24, 41);
$s9->drawLine(0, 1);
$s9->movePenTo(300, -40);
$s9->setLeftFill();
$s9->setRightFill(0xff, 0xff, 0xff, 0xb3);
#$s9->setLine(0,0,0,0);
$s9->drawLine(0, 80);
$s9->drawLine(-152, -15);
$s9->drawLine(0, -1);
$s9->drawLine(2, -24);
$s9->drawLine(-2, -24);
$s9->drawLine(152, -16);
$s9->movePenTo(240, 185);
$s9->setLeftFill();
$s9->setRightFill(0xff, 0xff, 0xff, 0xc0);
#$s9->setLine(0,0,0,0);
$s9->drawLine(-124, -90);
$s9->drawCurve(16, -20, 8, -22);
$s9->drawLine(140, 63);
$s9->drawLine(-40, 69);
$s9->movePenTo(95, 116);
$s9->setLeftFill(0xff, 0xff, 0xff, 0xcc);
$s9->setRightFill();
#$s9->setLine(0,0,0,0);
$s9->drawLine(-41, 25);
$s9->drawLine(-1, 0);
$s9->drawLine(62, 139);
$s9->drawLine(70, -40);
$s9->drawLine(-90, -124);
$s9->movePenTo(24, 149);
$s9->setLeftFill(0xff, 0xff, 0xff, 0xe6);
$s9->setRightFill();
#$s9->setLine(0,0,0,0);
$s9->drawLine(-25, 2);
$s9->drawLine(-23, -2);
$s9->drawLine(-16, 151);
$s9->drawLine(80, 0);
$s9->drawLine(-15, -151);
$s9->drawLine(-1, 0);
$s9->movePenTo(-280, -115);
$s9->setLeftFill(0xff, 0xff, 0xff, 0x73);
$s9->setRightFill();
#$s9->setLine(0,0,0,0);
$s9->drawLine(139, 62);
$s9->drawCurve(8, -23, 17, -19);
$s9->drawLine(-124, -89);
$s9->drawLine(-40, 69);
$s9->movePenTo(-53, -140);
$s9->setLeftFill(0xff, 0xff, 0xff, 0x80);
$s9->setRightFill();
#$s9->setLine(0,0,0,0);
$s9->drawLine(-62, -140);
$s9->drawLine(-70, 41);
$s9->drawLine(90, 123);
$s9->drawCurve(19, -16, 23, -8);
$s9->movePenTo(-300, -40);
$s9->setLeftFill(0xff, 0xff, 0xff, 0x66);
$s9->setRightFill();
#$s9->setLine(0,0,0,0);
$s9->drawLine(0, 80);
$s9->drawLine(152, -15);
$s9->drawLine(0, -1);
$s9->drawLine(-2, -24);
$s9->drawLine(2, -24);
$s9->drawLine(-152, -16);
$s9->movePenTo(-95, 117);
$s9->setLeftFill(0xff, 0xff, 0xff);
$s9->setRightFill();
#$s9->setLine(0,0,0,0);
$s9->drawLine(-89, 123);
$s9->drawLine(69, 40);
$s9->drawLine(62, -138);
$s9->drawCurve(-23, -9, -19, -16);
$s9->movePenTo(-141, 54);
$s9->setLeftFill(0xff, 0xff, 0xff, 0x5a);
$s9->setRightFill();
#$s9->setLine(0,0,0,0);
$s9->drawLine(-139, 62);
$s9->drawLine(41, 69);
$s9->drawLine(123, -89);
$s9->drawLine(-25, -42);

### Shape 10 ###
$s10 = new SWF::Shape();
$s10->movePenTo(95, -115);
$s10->setLeftFill(0xff, 0xff, 0xff, 0x8d);
$s10->setRightFill();
#$s10->setLine(0,0,0,0);
$s10->drawLine(90, -124);
$s10->drawLine(-70, -40);
$s10->drawLine(-62, 139);
$s10->drawLine(41, 25);
$s10->drawLine(1, 0);
$s10->movePenTo(140, -52);
$s10->setLeftFill(0xff, 0xff, 0xff, 0x99);
$s10->setRightFill();
#$s10->setLine(0,0,0,0);
$s10->drawLine(140, -63);
$s10->drawLine(-40, -69);
$s10->drawLine(-124, 90);
$s10->drawLine(24, 41);
$s10->drawLine(0, 1);
$s10->movePenTo(300, -40);
$s10->setLeftFill();
$s10->setRightFill(0xff, 0xff, 0xff, 0xa6);
#$s10->setLine(0,0,0,0);
$s10->drawLine(0, 81);
$s10->drawLine(-152, -16);
$s10->drawLine(0, -1);
$s10->drawLine(2, -24);
$s10->drawLine(-2, -24);
$s10->drawLine(152, -16);
$s10->movePenTo(40, -300);
$s10->setLeftFill(0xff, 0xff, 0xff, 0x80);
$s10->setRightFill();
#$s10->setLine(0,0,0,0);
$s10->drawLine(-80, 0);
$s10->drawLine(16, 152);
$s10->drawLine(23, -1);
$s10->drawLine(25, 2);
$s10->drawLine(16, -153);
$s10->movePenTo(240, 185);
$s10->setLeftFill();
$s10->setRightFill(0xff, 0xff, 0xff, 0xb3);
#$s10->setLine(0,0,0,0);
$s10->drawLine(-124, -90);
$s10->drawCurve(16, -20, 8, -22);
$s10->drawLine(140, 62);
$s10->drawLine(-40, 70);
$s10->movePenTo(95, 116);
$s10->setLeftFill(0xff, 0xff, 0xff, 0xc0);
$s10->setRightFill();
#$s10->setLine(0,0,0,0);
$s10->drawLine(-41, 25);
$s10->drawLine(-1, 0);
$s10->drawLine(63, 139);
$s10->drawLine(69, -40);
$s10->drawLine(-90, -124);
$s10->movePenTo(24, 149);
$s10->setLeftFill(0xff, 0xff, 0xff, 0xcc);
$s10->setRightFill();
#$s10->setLine(0,0,0,0);
$s10->drawLine(-25, 2);
$s10->drawLine(-23, -2);
$s10->drawLine(-16, 151);
$s10->drawLine(80, 0);
$s10->drawLine(-15, -151);
$s10->drawLine(-1, 0);
$s10->movePenTo(-280, -115);
$s10->setLeftFill(0xff, 0xff, 0xff, 0x66);
$s10->setRightFill();
#$s10->setLine(0,0,0,0);
$s10->drawLine(139, 62);
$s10->drawCurve(8, -23, 16, -19);
$s10->drawLine(-123, -89);
$s10->drawLine(-40, 69);
$s10->movePenTo(-53, -140);
$s10->setLeftFill(0xff, 0xff, 0xff, 0x73);
$s10->setRightFill();
#$s10->setLine(0,0,0,0);
$s10->drawLine(-62, -140);
$s10->drawLine(-69, 40);
$s10->drawLine(89, 124);
$s10->drawCurve(19, -16, 23, -8);
$s10->movePenTo(-300, -40);
$s10->setLeftFill(0xff, 0xff, 0xff, 0x5a);
$s10->setRightFill();
#$s10->setLine(0,0,0,0);
$s10->drawLine(0, 81);
$s10->drawLine(152, -16);
$s10->drawLine(0, -1);
$s10->drawLine(-2, -24);
$s10->drawLine(2, -24);
$s10->drawLine(-152, -16);
$s10->movePenTo(-141, 53);
$s10->setLeftFill(0xff, 0xff, 0xff);
$s10->setRightFill();
#$s10->setLine(0,0,0,0);
$s10->drawLine(-139, 63);
$s10->drawLine(40, 69);
$s10->drawLine(124, -89);
$s10->drawLine(-25, -43);
$s10->movePenTo(-53, 142);
$s10->setLeftFill(0xff, 0xff, 0xff, 0xe6);
$s10->setRightFill();
#$s10->setLine(0,0,0,0);
$s10->drawCurve(-23, -9, -19, -16);
$s10->drawLine(-89, 123);
$s10->drawLine(69, 40);
$s10->drawLine(62, -138);

### Shape 11 ###
$s11 = new SWF::Shape();
$s11->movePenTo(25, -147);
$s11->setLeftFill(0xff, 0xff, 0xff, 0x73);
$s11->setRightFill();
#$s11->setLine(0,0,0,0);
$s11->drawLine(15, -153);
$s11->drawLine(-80, 0);
$s11->drawLine(16, 152);
$s11->drawLine(23, -1);
$s11->drawLine(25, 2);
$s11->drawLine(1, 0);
$s11->movePenTo(95, -115);
$s11->setLeftFill(0xff, 0xff, 0xff, 0x80);
$s11->setRightFill();
#$s11->setLine(0,0,0,0);
$s11->drawLine(90, -125);
$s11->drawLine(-70, -40);
$s11->drawLine(-62, 140);
$s11->drawLine(41, 25);
$s11->drawLine(1, 0);
$s11->movePenTo(140, -52);
$s11->setLeftFill(0xff, 0xff, 0xff, 0x8d);
$s11->setRightFill();
#$s11->setLine(0,0,0,0);
$s11->drawLine(140, -63);
$s11->drawLine(-40, -69);
$s11->drawLine(-124, 90);
$s11->drawLine(24, 41);
$s11->drawLine(0, 1);
$s11->movePenTo(300, -40);
$s11->setLeftFill();
$s11->setRightFill(0xff, 0xff, 0xff, 0x99);
#$s11->setLine(0,0,0,0);
$s11->drawLine(0, 80);
$s11->drawLine(-152, -15);
$s11->drawLine(0, -1);
$s11->drawLine(2, -24);
$s11->drawLine(-2, -24);
$s11->drawLine(152, -16);
$s11->movePenTo(240, 185);
$s11->setLeftFill();
$s11->setRightFill(0xff, 0xff, 0xff, 0xa6);
#$s11->setLine(0,0,0,0);
$s11->drawLine(-124, -90);
$s11->drawCurve(16, -20, 8, -22);
$s11->drawLine(140, 63);
$s11->drawLine(-40, 69);
$s11->movePenTo(53, 141);
$s11->setLeftFill(0xff, 0xff, 0xff, 0xb3);
$s11->setRightFill();
#$s11->setLine(0,0,0,0);
$s11->drawLine(62, 139);
$s11->drawLine(70, -40);
$s11->drawLine(-90, -124);
$s11->drawLine(-41, 25);
$s11->drawLine(-1, 0);
$s11->movePenTo(24, 149);
$s11->setLeftFill(0xff, 0xff, 0xff, 0xc0);
$s11->setRightFill();
#$s11->setLine(0,0,0,0);
$s11->drawLine(-25, 2);
$s11->drawLine(-23, -2);
$s11->drawLine(-16, 151);
$s11->drawLine(80, 0);
$s11->drawLine(-15, -151);
$s11->drawLine(-1, 0);
$s11->movePenTo(-280, -115);
$s11->setLeftFill(0xff, 0xff, 0xff, 0x5a);
$s11->setRightFill();
#$s11->setLine(0,0,0,0);
$s11->drawLine(139, 62);
$s11->drawCurve(8, -23, 17, -19);
$s11->drawLine(-124, -89);
$s11->drawLine(-40, 69);
$s11->movePenTo(-53, -140);
$s11->setLeftFill(0xff, 0xff, 0xff, 0x66);
$s11->setRightFill();
#$s11->setLine(0,0,0,0);
$s11->drawLine(-62, -140);
$s11->drawLine(-69, 40);
$s11->drawLine(89, 124);
$s11->drawCurve(19, -16, 23, -8);
$s11->movePenTo(-300, -40);
$s11->setLeftFill(0xff, 0xff, 0xff);
$s11->setRightFill();
#$s11->setLine(0,0,0,0);
$s11->drawLine(0, 80);
$s11->drawLine(152, -15);
$s11->drawLine(0, -1);
$s11->drawLine(-2, -24);
$s11->drawLine(2, -24);
$s11->drawLine(-152, -16);
$s11->movePenTo(-95, 117);
$s11->setLeftFill(0xff, 0xff, 0xff, 0xcc);
$s11->setRightFill();
#$s11->setLine(0,0,0,0);
$s11->drawLine(-90, 123);
$s11->drawLine(70, 40);
$s11->drawLine(62, -138);
$s11->drawCurve(-23, -9, -19, -16);
$s11->movePenTo(-140, 54);
$s11->setLeftFill(0xff, 0xff, 0xff, 0xe6);
$s11->setRightFill();
#$s11->setLine(0,0,0,0);
$s11->drawLine(-140, 62);
$s11->drawLine(40, 69);
$s11->drawLine(124, -89);
$s11->drawCurve(-17, -20, -7, -22);

### Shape 12 ###
$s12 = new SWF::Shape();
$s12->movePenTo(95, -115);
$s12->setLeftFill(0xff, 0xff, 0xff, 0x73);
$s12->setRightFill();
#$s12->setLine(0,0,0,0);
$s12->drawLine(90, -125);
$s12->drawLine(-69, -40);
$s12->drawLine(-63, 140);
$s12->drawLine(41, 25);
$s12->drawLine(1, 0);
$s12->movePenTo(140, -53);
$s12->setLeftFill(0xff, 0xff, 0xff, 0x80);
$s12->setRightFill();
#$s12->setLine(0,0,0,0);
$s12->drawLine(140, -62);
$s12->drawLine(-40, -70);
$s12->drawLine(-124, 90);
$s12->drawLine(0, 1);
$s12->drawLine(24, 41);
$s12->movePenTo(300, -40);
$s12->setLeftFill();
$s12->setRightFill(0xff, 0xff, 0xff, 0x8d);
#$s12->setLine(0,0,0,0);
$s12->drawLine(0, 80);
$s12->drawLine(-152, -15);
$s12->drawLine(0, -1);
$s12->drawLine(2, -24);
$s12->drawLine(-2, -24);
$s12->drawLine(152, -16);
$s12->movePenTo(40, -300);
$s12->setLeftFill(0xff, 0xff, 0xff, 0x66);
$s12->setRightFill();
#$s12->setLine(0,0,0,0);
$s12->drawLine(-80, 0);
$s12->drawLine(16, 152);
$s12->drawLine(23, -1);
$s12->drawLine(25, 2);
$s12->drawLine(16, -153);
$s12->movePenTo(240, 185);
$s12->setLeftFill();
$s12->setRightFill(0xff, 0xff, 0xff, 0x99);
#$s12->setLine(0,0,0,0);
$s12->drawLine(-124, -90);
$s12->drawCurve(16, -20, 8, -22);
$s12->drawLine(140, 63);
$s12->drawLine(-40, 69);
$s12->movePenTo(95, 116);
$s12->setLeftFill(0xff, 0xff, 0xff, 0xa6);
$s12->setRightFill();
#$s12->setLine(0,0,0,0);
$s12->drawLine(-41, 25);
$s12->drawLine(-1, 0);
$s12->drawLine(62, 139);
$s12->drawLine(70, -40);
$s12->drawLine(-90, -124);
$s12->movePenTo(40, 300);
$s12->setLeftFill();
$s12->setRightFill(0xff, 0xff, 0xff, 0xb3);
#$s12->setLine(0,0,0,0);
$s12->drawLine(-80, 0);
$s12->drawLine(16, -151);
$s12->drawLine(23, 2);
$s12->drawLine(25, -2);
$s12->drawLine(16, 151);
$s12->movePenTo(-280, -115);
$s12->setLeftFill(0xff, 0xff, 0xff);
$s12->setRightFill();
#$s12->setLine(0,0,0,0);
$s12->drawLine(139, 62);
$s12->drawCurve(8, -23, 17, -19);
$s12->drawLine(-124, -89);
$s12->drawLine(-40, 69);
$s12->movePenTo(-53, -140);
$s12->setLeftFill(0xff, 0xff, 0xff, 0x5a);
$s12->setRightFill();
#$s12->setLine(0,0,0,0);
$s12->drawLine(-62, -140);
$s12->drawLine(-70, 41);
$s12->drawLine(90, 123);
$s12->drawCurve(19, -16, 23, -8);
$s12->movePenTo(-300, -40);
$s12->setLeftFill(0xff, 0xff, 0xff, 0xe6);
$s12->setRightFill();
#$s12->setLine(0,0,0,0);
$s12->drawLine(0, 80);
$s12->drawLine(152, -15);
$s12->drawLine(0, -1);
$s12->drawLine(-2, -24);
$s12->drawLine(2, -24);
$s12->drawLine(-152, -16);
$s12->movePenTo(-95, 117);
$s12->setLeftFill(0xff, 0xff, 0xff, 0xc0);
$s12->setRightFill();
#$s12->setLine(0,0,0,0);
$s12->drawLine(-89, 123);
$s12->drawLine(69, 40);
$s12->drawLine(62, -138);
$s12->drawCurve(-23, -9, -19, -16);
$s12->movePenTo(-141, 53);
$s12->setLeftFill(0xff, 0xff, 0xff, 0xcc);
$s12->setRightFill();
#$s12->setLine(0,0,0,0);
$s12->drawLine(-139, 62);
$s12->drawLine(40, 70);
$s12->drawLine(124, -89);
$s12->drawLine(-25, -43);

### Shape 13 ###
$s13 = new SWF::Shape();
$s13->movePenTo(140, -52);
$s13->setLeftFill(0xff, 0xff, 0xff, 0x73);
$s13->setRightFill();
#$s13->setLine(0,0,0,0);
$s13->drawLine(140, -63);
$s13->drawLine(-40, -69);
$s13->drawLine(-124, 89);
$s13->drawLine(0, 1);
$s13->drawLine(24, 41);
$s13->drawLine(0, 1);
$s13->movePenTo(94, -115);
$s13->setLeftFill(0xff, 0xff, 0xff, 0x66);
$s13->setRightFill();
#$s13->setLine(0,0,0,0);
$s13->drawLine(91, -125);
$s13->drawLine(-69, -40);
$s13->drawLine(-63, 140);
$s13->drawLine(41, 25);
$s13->movePenTo(148, -24);
$s13->setLeftFill(0xff, 0xff, 0xff, 0x80);
$s13->setRightFill();
#$s13->setLine(0,0,0,0);
$s13->drawLine(2, 24);
$s13->drawLine(-2, 24);
$s13->drawLine(152, 16);
$s13->drawLine(0, -80);
$s13->drawLine(-152, 16);
$s13->movePenTo(40, -300);
$s13->setLeftFill(0xff, 0xff, 0xff, 0x5a);
$s13->setRightFill();
#$s13->setLine(0,0,0,0);
$s13->drawLine(-80, 0);
$s13->drawLine(16, 152);
$s13->drawLine(23, -1);
$s13->drawLine(25, 2);
$s13->drawLine(16, -153);
$s13->movePenTo(240, 185);
$s13->setLeftFill();
$s13->setRightFill(0xff, 0xff, 0xff, 0x8d);
#$s13->setLine(0,0,0,0);
$s13->drawLine(-124, -90);
$s13->drawCurve(16, -20, 8, -22);
$s13->drawLine(140, 62);
$s13->drawLine(-40, 70);
$s13->movePenTo(95, 116);
$s13->setLeftFill(0xff, 0xff, 0xff, 0x99);
$s13->setRightFill();
#$s13->setLine(0,0,0,0);
$s13->drawLine(-41, 25);
$s13->drawLine(-1, 0);
$s13->drawLine(63, 139);
$s13->drawLine(69, -40);
$s13->drawLine(-90, -124);
$s13->movePenTo(24, 149);
$s13->setLeftFill(0xff, 0xff, 0xff, 0xa6);
$s13->setRightFill();
#$s13->setLine(0,0,0,0);
$s13->drawLine(-25, 2);
$s13->drawLine(-23, -2);
$s13->drawLine(-16, 151);
$s13->drawLine(80, 0);
$s13->drawLine(-15, -151);
$s13->drawLine(-1, 0);
$s13->movePenTo(-280, -115);
$s13->setLeftFill(0xff, 0xff, 0xff, 0xe6);
$s13->setRightFill();
#$s13->setLine(0,0,0,0);
$s13->drawLine(139, 62);
$s13->drawCurve(8, -23, 16, -19);
$s13->drawLine(-123, -89);
$s13->drawLine(-40, 69);
$s13->movePenTo(-53, -140);
$s13->setLeftFill(0xff, 0xff, 0xff);
$s13->setRightFill();
#$s13->setLine(0,0,0,0);
$s13->drawLine(-62, -140);
$s13->drawLine(-69, 40);
$s13->drawLine(89, 124);
$s13->drawCurve(19, -16, 23, -8);
$s13->movePenTo(-148, -24);
$s13->setLeftFill();
$s13->setRightFill(0xff, 0xff, 0xff, 0xcc);
#$s13->setLine(0,0,0,0);
$s13->drawLine(-2, 24);
$s13->drawLine(2, 24);
$s13->drawLine(0, 1);
$s13->drawLine(-152, 15);
$s13->drawLine(0, -80);
$s13->drawLine(152, 16);
$s13->movePenTo(-95, 117);
$s13->setLeftFill(0xff, 0xff, 0xff, 0xb3);
$s13->setRightFill();
#$s13->setLine(0,0,0,0);
$s13->drawLine(-89, 123);
$s13->drawLine(69, 40);
$s13->drawLine(62, -138);
$s13->drawCurve(-23, -9, -19, -16);
$s13->movePenTo(-141, 53);
$s13->setLeftFill(0xff, 0xff, 0xff, 0xc0);
$s13->setRightFill();
#$s13->setLine(0,0,0,0);
$s13->drawLine(-139, 63);
$s13->drawLine(40, 69);
$s13->drawLine(124, -89);
$s13->drawLine(-25, -43);

### MovieClip 1 ###
$s1 = new SWF::MovieClip();  # 14 frames
$s1->add(new SWF::Action("
this.gotoAndPlay(random(12) + 1);
;

"));
$j1 = $s1->add($s2);
$j1->multColor(1.000000, 1.000000, 1.000000);
$j1->addColor(0x00, 0x00, 0x00);
$s1->nextFrame();  # end of clip frame 1 

$s1->nextFrame();  # end of clip frame 2 

$s1->remove($j1);
$j1 = $s1->add($s3);
$j1->multColor(1.000000, 1.000000, 1.000000);
$j1->addColor(0x00, 0x00, 0x00);
$s1->nextFrame();  # end of clip frame 3 

$s1->remove($j1);
$j1 = $s1->add($s4);
$j1->multColor(1.000000, 1.000000, 1.000000);
$j1->addColor(0x00, 0x00, 0x00);
$s1->nextFrame();  # end of clip frame 4 

$s1->remove($j1);
$j1 = $s1->add($s5);
$j1->multColor(1.000000, 1.000000, 1.000000);
$j1->addColor(0x00, 0x00, 0x00);
$s1->nextFrame();  # end of clip frame 5 

$s1->remove($j1);
$j1 = $s1->add($s6);
$j1->multColor(1.000000, 1.000000, 1.000000);
$j1->addColor(0x00, 0x00, 0x00);
$s1->nextFrame();  # end of clip frame 6 

$s1->remove($j1);
$j1 = $s1->add($s7);
$j1->multColor(1.000000, 1.000000, 1.000000);
$j1->addColor(0x00, 0x00, 0x00);
$s1->nextFrame();  # end of clip frame 7 

$s1->remove($j1);
$j1 = $s1->add($s8);
$j1->multColor(1.000000, 1.000000, 1.000000);
$j1->addColor(0x00, 0x00, 0x00);
$s1->nextFrame();  # end of clip frame 8 

$s1->remove($j1);
$j1 = $s1->add($s9);
$j1->multColor(1.000000, 1.000000, 1.000000);
$j1->addColor(0x00, 0x00, 0x00);
$s1->nextFrame();  # end of clip frame 9 

$s1->remove($j1);
$j1 = $s1->add($s10);
$j1->multColor(1.000000, 1.000000, 1.000000);
$j1->addColor(0x00, 0x00, 0x00);
$s1->nextFrame();  # end of clip frame 10 

$s1->remove($j1);
$j1 = $s1->add($s11);
$j1->multColor(1.000000, 1.000000, 1.000000);
$j1->addColor(0x00, 0x00, 0x00);
$s1->nextFrame();  # end of clip frame 11 

$s1->remove($j1);
$j1 = $s1->add($s12);
$j1->multColor(1.000000, 1.000000, 1.000000);
$j1->addColor(0x00, 0x00, 0x00);
$s1->nextFrame();  # end of clip frame 12 

$s1->remove($j1);
$j1 = $s1->add($s13);
$j1->multColor(1.000000, 1.000000, 1.000000);
$j1->addColor(0x00, 0x00, 0x00);
$s1->nextFrame();  # end of clip frame 13 

$s1->add(new SWF::Action("
this.gotoAndPlay(2);
;

"));
$s1->nextFrame();  # end of clip frame 14 

$s14 = new SWF::MovieClip();  # 1 frames
$j1 = $s14->add($s1);
$j1->scaleTo(0.666672);
$j1->moveTo(200, 200);
$j1->multColor(0.000000, 0.000000, 0.000000);
$j1->addColor(0x81, 0xac, 0xdb);
$s14->nextFrame();  # end of clip frame 1 


$i1 = $progressclip->add($s14);
$i1->moveTo(20, 20);
$i1->multColor(1.000000, 1.000000, 1.000000);
$i1->addColor(0x00, 0x00, 0x00);
$progressclip->nextFrame();  # end of frame 1


$i1 = $movie->add($progressclip);
$i1->scaleTo(0.5); 
$i1->moveTo(4700,2550);
$movie->nextFrame();
$movie->nextFrame();
$movie->nextFrame();


$movie->add(new SWF::Action(<<ENDSCRIPT

//System.useCodePage();

fmtLoad = new TextFormat();
fmtLoad.size = 20;
fmtLoad.font = "$fuente_nombre";
fmtLoad.color = "0x3240da";

_root.createTextField('preload',getNextHighestDepth(),500,252,400,100);
_root.preload.setNewTextFormat(fmtLoad);   
with (_root['preload']) {
	border = false;
	embedFonts = true;
}

createEmptyMovieClip("fondo",-15000);
fondo.createEmptyMovieClip("mihijo",0);
loadMovie("background.jpg", fondo.mihijo);
fondo._visible = true;
fondo._x = 1;
fondo._y = 1;
_root.textoload._visible=false;

if (fondo.mihijo.getBytesLoaded() == fondo.mihijo.getBytesTotal() && fonto.mihijo.getBytesLoaded() > 4) {
		_root.preload.text="background";
}


var totalTotal  = _root.getBytesTotal() + fondo.mihijo.getBytesTotal();
var totalLoaded = _root.getBytesLoaded() + fondo.mihijo.getBytesLoaded();


if (totalLoaded < totalTotal){
	var percent = Math.floor(totalLoaded*100/totalTotal);
	//_root.preload.text=percent+ "% ";
	prevFrame();
 	play();
} else {
	_root.preload.text="";
	nextFrame();
}

ENDSCRIPT
));
$movie->nextFrame( );
$movie->remove($i1);

$movie->add(new SWF::Action(<<"EndOfActionScript"));

_root.createEmptyMovieClip("soundHolder", -14345);
_root.soundHolder.mySound = new Sound(_root.soundHolder);
//_root.soundHolder.mySound.loadSound("pepe.mp3", true);
//_root.soundHolder.mySound.stop();

dummyVar=(getTimer()+random(100000));

if(context != undefined) {
	context = context.toUpperCase();
	if(context == "DEFAULT") {	context=""; }
} else {
	context="";
}

if(nohighlight != undefined) {
    _global.nohighlight = Number(nohighlight);
} else {
    _global.nohighlight = 0;
}

if(mybutton != undefined) {
    mybutton = Number(mybutton);
}

if(restrict != undefined) {
	_global.restrict = restrict;
} else {
	delete _global.restrict;
}

if(dial != undefined) {
//	dial = Number(dial);
	dial = dial;
	_root.logea("dial "+dial);
} else {
	dial = 0;
}

var archivo = "variables"+context+".txt?aldope="+dummyVar;

vr = new LoadVars ();

vr.onLoad = function (success)
{ 
	if (success == true) { 
		nextFrame();
	} else { 
			with (_root['preload']) {
			    text = "Couldn't load "+archivo;
				errorconfiguration=1;
			    multiline = true;
				wordWrap = true;
//			    border = true;
//				prevFrame();
			}
		stop();
	}
};

vr.load(archivo);

EndOfActionScript

$movie->nextFrame();

#$font_general = new SWF::Font($fuente);  

# Ventana INPUT del security code
$codebox = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$g = new SWF::Gradient();
$g->addEntry(0.000000, 0xff, 0xff, 0xff);
$g->addEntry(0.015686, 0xe2, 0xe2, 0xe2);
$g->addEntry(0.964706, 0x9d, 0x9d, 0x9d);
$g->addEntry(1.000000, 0x5a, 0x5a, 0x5a);
$f2 = $s1->addFill($g, SWFFILL_LINEAR_GRADIENT);
$f2->scaleTo(0.24, 0.24);
$f2->moveTo(-35, 37);
$s1->movePenTo(2074, -1915);
$s1->setLeftFill(0x66, 0x66, 0x66, 0x39);
$s1->setLine(20, 0xc5, 0xc5, 0xc5, 0x39);
$s1->drawCurve(-29, -31, -41, 0);
$s1->drawLine(-3939, 0);
$s1->drawCurve(-41, 0, -30, 31);
$s1->drawCurve(-29, 32, 0, 44);
$s1->drawLine(0, 3945);
$s1->drawCurve(0, 44, 29, 31);
$s1->drawCurve(30, 33, 41, 0);
$s1->drawLine(3939, 0);
$s1->drawCurve(41, 0, 29, -33);
$s1->drawCurve(30, -31, 0, -44);
$s1->drawLine(0, -3945);
$s1->drawCurve(0, -44, -30, -32);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(2005, -2023);
$s1->setLeftFill($f2);
$s1->setLine(20, 0x66, 0x66, 0x66);
$s1->drawCurve(-29, -31, -41, 0);
$s1->drawLine(-3939, 0);
$s1->drawCurve(-41, 0, -30, 31);
$s1->drawCurve(-29, 33, 0, 44);
$s1->drawLine(0, 3966);
$s1->drawCurve(0, 44, 29, 32);
$s1->drawCurve(30, 32, 41, 0);
$s1->drawLine(3939, 0);
$s1->drawCurve(41, 0, 29, -32);
$s1->drawCurve(30, -32, 0, -44);
$s1->drawLine(0, -3966);
$s1->drawCurve(0, -44, -30, -33);

### MovieClip 2 ###
$s2 = new SWF::MovieClip();  # 1 frames
$s2->add($s1);
$s2->nextFrame();  # end of clip frame 1 


$i1 = $codebox->add($s2);
$i1->scaleTo(1.427750, 0.540558);
$i1->moveTo(5509, 3957);
$i1->setName('inputCode');

### Shape 3 ###
$s3 = new SWF::Shape();
$s3->movePenTo(3094, 4290);
$s3->setRightFill(0xcc, 0xcc, 0xcc);
$s3->setLine(20, 0x99, 0x99, 0x99);
$s3->drawLine(0, -580);
$s3->drawLine(4713, 0);
$s3->setLine(20, 0xcc, 0xcc, 0xcc);
$s3->drawLine(0, 580);
$s3->drawLine(-4713, 0);
$i4 = $codebox->add($s3);

$s5 = new SWF::TextField(SWFTEXTFIELD_NOEDIT | SWFTEXTFIELD_MULTILINE | SWFTEXTFIELD_NOSELECT | SWFTEXTFIELD_USEFONT );
$s5->setBounds(5650, 653);
$s5->setFont($font_general);
$s5->setHeight(320);
$s5->setColor(0x00, 0x00, 0x00, 0xff);
$s5->align(SWFTEXTFIELD_ALIGN_CENTER);
$s5->setName('title');
$s5->addString('Please enter the security code:');
$i5 = $codebox->add($s5);
$i5->moveTo(2704, 3067);

$s6 = new SWF::TextField( SWFTEXTFIELD_PASSWORD | SWFTEXTFIELD_USEFONT );
$s6->setBounds(4579, 398);
$s6->setFont($font_general);
$s6->setHeight(320);
$s6->setColor(0x00, 0x00, 0x00, 0xff);
$s6->align(SWFTEXTFIELD_ALIGN_LEFT);
$i6 = $codebox->add($s6);
$i6->moveTo(3189, 3821);
$i6->setName('claveform');

### Shape 7 ###
$s7 = new SWF::Shape();
$s7->movePenTo(-500, 200);
$s7->setRightFill(0xcc, 0xcc, 0xcc);
$s7->setLine(20, 0x99, 0x99, 0x99);
$s7->drawLine(0, -400);
$s7->drawLine(1000, 0);
$s7->setLine(20, 0x00, 0x00, 0x00);
$s7->drawLine(0, 400);
$s7->drawLine(-1000, 0);

$s8 = new SWF::Text;
$s8->setFont($font_general);
$s8->setHeight(280);
$s8->setColor(0x00, 0x00, 0x33, 0xff);
$s8->moveTo(-180,100);
$s8->addString('OK');

### Shape 9 ###
$s9 = new SWF::Shape();
$s9->movePenTo(-500, 200);
$s9->setRightFill(0xcc, 0xcc, 0xcc);
$s9->setLine(20, 0x99, 0x99, 0x99);
$s9->drawLine(0, -400);
$s9->drawLine(1000, 0);
$s9->setLine(20, 0x00, 0x00, 0x00);
$s9->drawLine(0, 400);
$s9->drawLine(-1000, 0);

$s11 = new SWF::Shape();
$s11->movePenTo(500, -200);
$s11->setRightFill(0xcc, 0xcc, 0xcc);
$s11->setLine(20, 0x99, 0x99, 0x99);
$s11->drawLine(0, 400);
$s11->drawLine(-1000, 0);
$s11->setLine(20, 0x00, 0x00, 0x00);
$s11->drawLine(0, -400);
$s11->drawLine(1000, 0);


### Button2 13 ###
$s13 = new SWF::Button();
$s13->addShape($s7, SWFBUTTON_UP);
$s13->addShape($s8, SWFBUTTON_UP);
$s13->addShape($s9, SWFBUTTON_OVER);
$s13->addShape($s8, SWFBUTTON_OVER);
$s13->addShape($s11, SWFBUTTON_HIT | SWFBUTTON_DOWN);
$s13->addShape($s8, SWFBUTTON_HIT | SWFBUTTON_DOWN);
$a = new SWF::Action("
_global.claveingresada = this.claveform.text;
_root.LocalSave('auth','clave',_global.claveingresada);
this._visible = false;
_root.envia_comando('bogus', 0, 0);
;
");
$s13->addAction($a, SWFBUTTON_MOUSEUP);

$i7 = $codebox->add($s13);
$i7->moveTo(5454, 4725);

### Shape 14 ###
$s14 = new SWF::Shape();
$s14->movePenTo(228, -228);
$s14->setRightFill(0x99, 0x99, 0x99);
$s14->setLine(20, 0x00, 0x00, 0x00);
$s14->drawLine(0, 456);
$s14->drawLine(-456, 0);
$s14->setLine(20, 0xcc, 0xcc, 0xcc);
$s14->drawLine(0, -456);
$s14->drawLine(456, 0);
$s14->setLeftFill();
$s14->setRightFill();
$s14->movePenTo(120, 132);
$s14->setLine(60, 0xcc, 0xcc, 0xcc);
$s14->drawLine(-120, -117);
$s14->drawLine(-112, 121);
$s14->movePenTo(-120, -102);
$s14->drawLine(120, 117);
$s14->drawLine(112, -120);
$s14->setLeftFill();
$s14->setRightFill();
$s14->movePenTo(120, 102);
$s14->setLine(60, 0x00, 0x00, 0x00);
$s14->drawLine(-120, -117);
$s14->drawLine(-112, 121);
$s14->movePenTo(-120, -132);
$s14->drawLine(120, 117);
$s14->drawLine(112, -120);

### Shape 15 ###
$s15 = new SWF::Shape();
$s15->movePenTo(228, -228);
$s15->setRightFill(0x99, 0x99, 0x99);
$s15->setLine(20, 0x00, 0x00, 0x00);
$s15->drawLine(0, 456);
$s15->drawLine(-456, 0);
$s15->setLine(20, 0xcc, 0xcc, 0xcc);
$s15->drawLine(0, -456);
$s15->drawLine(456, 0);
$s15->setLeftFill();
$s15->setRightFill();
$s15->movePenTo(120, 132);
$s15->setLine(60, 0xcc, 0xcc, 0xcc);
$s15->drawLine(-120, -117);
$s15->drawLine(-112, 121);
$s15->movePenTo(-120, -102);
$s15->drawLine(120, 117);
$s15->drawLine(112, -120);
$s15->setLeftFill();
$s15->setRightFill();
$s15->movePenTo(120, 102);
$s15->setLine(60, 0x00, 0x00, 0x00);
$s15->drawLine(-120, -117);
$s15->drawLine(-112, 121);
$s15->movePenTo(-120, -132);
$s15->drawLine(120, 117);
$s15->drawLine(112, -120);

### Shape 16 ###
$s16 = new SWF::Shape();
$s16->movePenTo(228, -228);
$s16->setRightFill(0x99, 0x99, 0x99);
$s16->setLine(20, 0x00, 0x00, 0x00);
$s16->drawLine(0, 456);
$s16->drawLine(-456, 0);
$s16->setLine(20, 0xcc, 0xcc, 0xcc);
$s16->drawLine(0, -456);
$s16->drawLine(456, 0);
$s16->setLeftFill();
$s16->setRightFill();
$s16->movePenTo(120, 132);
$s16->setLine(60, 0xcc, 0xcc, 0xcc);
$s16->drawLine(-120, -117);
$s16->drawLine(-112, 121);
$s16->movePenTo(-120, -102);
$s16->drawLine(120, 117);
$s16->drawLine(112, -120);
$s16->setLeftFill();
$s16->setRightFill();
$s16->movePenTo(122, 120);
$s16->setLine(60, 0x00, 0x00, 0x00);
$s16->drawLine(-120, -117);
$s16->drawLine(-112, 121);
$s16->movePenTo(-118, -114);
$s16->drawLine(120, 117);
$s16->drawLine(112, -120);

### Button2 17 ###
$s17 = new SWF::Button();
$s17->addShape($s14, SWFBUTTON_UP);
$s17->addShape($s15, SWFBUTTON_OVER);
$s17->addShape($s16, SWFBUTTON_DOWN);
$s17->addShape($s16, SWFBUTTON_HIT);
$a = new SWF::Action("
this._visible = false;
");
$s17->addAction($a, SWFBUTTON_MOUSEUP);
$i10 = $codebox->add($s17);
$i10->scaleTo(0.657883);
$i10->moveTo(8118, 3100);


$codebox->nextFrame();  # end of frame 1

# FIN DE Ventana INPUT del security code

$i1=$movie->add($codebox);
$i1->scaleTo(0.5);
$i1->setDepth(101);
$i1->moveTo(2400,500);
$i1->setName("codebox");

$fin = $movie->add($dropbox1);    # XXXX
$fin->setName("selectbox1");      
$fin->moveTo(8000,60);
#
# Number Dialer Catcher
#
# We can control movie playback from javascript, but just
# the frame number. This bogus movieclip sets a global variable
# with the number constructed from javascript while setting
# the frame of the movieclip.
#
$numdial = new SWF::Sprite();
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	_global.numero_a_discar+="0";
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 1
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	_global.numero_a_discar+="1";
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 2
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	_global.numero_a_discar+="2";
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 3
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	_global.numero_a_discar+="3";
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 4
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	_global.numero_a_discar+="4";
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 5
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	_global.numero_a_discar+="5";
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 6
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	_global.numero_a_discar+="6";
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 7
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	_global.numero_a_discar+="7";
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 8
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	_global.numero_a_discar+="8";
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 9
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	_global.numero_a_discar+="9";
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 10
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	_global.numero_a_discar="";
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 11
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	if(_root.dial!=0) {
		_root.logea("Dial Number "+_global.numero_a_discar);
		_root.logea("Restrict "+_global.restrict);
		_root.logea("Dialing from "+_root.dial);
		if(_global.restrict != undefined)
		{
			_root.envia_comando("dial",_global.restrict,_global.numero_a_discar);
		} else {
			_root.envia_comando("dial",_root.dial,_global.numero_a_discar);
		}
	} else {
		_root.logea("Dial not defined in index.html "+numero_a_discar);
	}
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 13
$numdial->add(new SWF::Action(<<"EndOfActionScript"));
	stop();
EndOfActionScript
$numdial->nextFrame(); # End Frame 13

$i1 = $movie->add($numdial);
$i1->moveTo(-2000,-2000);
$i1->setName("numdial");




# Detail Window movieclip

$detail_window = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$g = new SWF::Gradient();
$g->addEntry(0.000000, 0xff, 0xff, 0xff);
$g->addEntry(0.015686, 0xe2, 0xe2, 0xe2);
$g->addEntry(0.964706, 0x9d, 0x9d, 0x9d);
$g->addEntry(1.000000, 0x5a, 0x5a, 0x5a);
$f2 = $s1->addFill($g, SWFFILL_LINEAR_GRADIENT);
$f2->scaleTo(0.24, 0.24);
$f2->moveTo(-35, 37);
$s1->movePenTo(2074, -1915);
$s1->setLeftFill(0x66, 0x66, 0x66, 0x39);
$s1->setLine(20, 0xc5, 0xc5, 0xc5, 0x39);
$s1->drawCurve(-29, -31, -41, 0);
$s1->drawLine(-3939, 0);
$s1->drawCurve(-41, 0, -30, 31);
$s1->drawCurve(-29, 32, 0, 44);
$s1->drawLine(0, 3945);
$s1->drawCurve(0, 44, 29, 31);
$s1->drawCurve(30, 33, 41, 0);
$s1->drawLine(3939, 0);
$s1->drawCurve(41, 0, 29, -33);
$s1->drawCurve(30, -31, 0, -44);
$s1->drawLine(0, -3945);
$s1->drawCurve(0, -44, -30, -32);
$s1->setLeftFill();
$s1->setRightFill();
$s1->setLine(0,0,0,0);
$s1->movePenTo(2005, -2023);
$s1->setLeftFill($f2);
$s1->setLine(20, 0x66, 0x66, 0x66);
$s1->drawCurve(-29, -31, -41, 0);
$s1->drawLine(-3939, 0);
$s1->drawCurve(-41, 0, -30, 31);
$s1->drawCurve(-29, 33, 0, 44);
$s1->drawLine(0, 3966);
$s1->drawCurve(0, 44, 29, 32);
$s1->drawCurve(30, 32, 41, 0);
$s1->drawLine(3939, 0);
$s1->drawCurve(41, 0, 29, -32);
$s1->drawCurve(30, -32, 0, -44);
$s1->drawLine(0, -3966);
$s1->drawCurve(0, -44, -30, -33);

$s3 = new SWF::TextField(SWFTEXTFIELD_NOEDIT | SWFTEXTFIELD_USEFONT );
$s3->setBounds(3112, 377);
$s3->setFont($font_general);
$s3->setHeight(280);
$s3->setColor(0x33, 0x33, 0x33, 0xff);
$s3->align(SWFTEXTFIELD_ALIGN_LEFT);
$s3->setName('title');
$s3->addString('Last call details:');

$s5 = new SWF::TextField(SWFTEXTFIELD_NOEDIT | SWFTEXTFIELD_USEFONT );
$s5->setBounds(907, 330);
$s5->setFont($font_general);
$s5->setHeight(260);
$s5->setColor(0x33, 0x33, 0x33, 0xff);
$s5->align(SWFTEXTFIELD_ALIGN_LEFT);
$s5->setName('label');
$s5->addString('From:');

$s6 = new SWF::TextField(SWFTEXTFIELD_NOEDIT | SWFTEXTFIELD_USEFONT );
$s6->setBounds(1097, 330);
$s6->setFont($font_general);
$s6->setHeight(260);
$s6->setColor(0x33, 0x33, 0x33, 0xff);
$s6->align(SWFTEXTFIELD_ALIGN_LEFT);
$s6->setName('duration_label');
$s6->addString('Duration:');

$s7 = new SWF::TextField(SWFTEXTFIELD_NOEDIT | SWFTEXTFIELD_USEFONT );
$s7->setBounds(2517, 330);
$s7->setFont($font_general);
$s7->setHeight(260);
$s7->setColor(0x33, 0x33, 0x33, 0xff);
$s7->align(SWFTEXTFIELD_ALIGN_LEFT);
$s7->setName('clid');

$s8 = new SWF::TextField(SWFTEXTFIELD_NOEDIT | SWFTEXTFIELD_USEFONT );
$s8->setBounds(2116, 330);
$s8->setFont($font_general);
$s8->setHeight(260);
$s8->setColor(0x33, 0x33, 0x33, 0xff);
$s8->align(SWFTEXTFIELD_ALIGN_LEFT);
$s8->setName('duration');

### Shape 9 ###
$s9 = new SWF::Shape();
$s9->movePenTo(142, -141);
$s9->setRightFill(0x33, 0x33, 0x33);
$s9->drawCurve(58, 58, 0, 83);
$s9->drawCurve(0, 83, -58, 59);
$s9->drawCurve(-59, 58, -83, 0);
$s9->drawCurve(-83, 0, -58, -58);
$s9->drawCurve(-59, -59, 0, -83);
$s9->drawCurve(0, -83, 59, -58);
$s9->drawCurve(58, -59, 83, 0);
$s9->drawCurve(83, 0, 59, 59);
$s9->setLeftFill();
$s9->setRightFill();
$s9->setLine(0,0,0,0);
$s9->movePenTo(-43, -137);
$s9->setRightFill(0xff, 0xff, 0xff);
$s9->drawLine(192, 136);
$s9->drawLine(-192, 139);
$s9->drawLine(0, -67);
$s9->drawLine(-66, -1);
$s9->drawLine(0, -143);
$s9->drawLine(66, 0);
$s9->drawLine(0, -64);

### Button2 10 ###
$s10 = new SWF::Button();
$s10->addShape($s9, SWFBUTTON_HIT | SWFBUTTON_DOWN | SWFBUTTON_OVER | SWFBUTTON_UP);
$a = new SWF::Action("
_root.superdetails._visible = true;
_root.detail._visible = false;
//_root.superdetails.tab1.gotoAndStop(1);
//_root.superdetails.tab2.gotoAndStop(2); 

				if(_global.superdetailstexttab1 == undefined) {
					_global.superdetailstexttab1 = 'no data';
				}
				if(_global.superdetailstexttab1 == 'no data') {
					_root.superdetails.tab1.gotoAndStop(2);
					_root.superdetails.tab2.gotoAndStop(1);
				    _root.superdetails.texto = _global.superdetailstexttab2;
				} else {
					_root.superdetails.tab1.gotoAndStop(1);
					_root.superdetails.tab2.gotoAndStop(2);
				    _root.superdetails.texto = _global.superdetailstexttab1;
				}
");
$s10->addAction($a, SWFBUTTON_MOUSEUP);

### Shape 11 ###
$s11 = new SWF::Shape();
$s11->movePenTo(228, -228);
$s11->setRightFill(0x99, 0x99, 0x99);
$s11->setLine(20, 0x00, 0x00, 0x00);
$s11->drawLine(0, 456);
$s11->drawLine(-456, 0);
$s11->setLine(20, 0xcc, 0xcc, 0xcc);
$s11->drawLine(0, -456);
$s11->drawLine(456, 0);
$s11->setLeftFill();
$s11->setRightFill();
$s11->setLine(0,0,0,0);
$s11->movePenTo(120, 132);
$s11->setLine(60, 0xcc, 0xcc, 0xcc);
$s11->drawLine(-120, -117);
$s11->drawLine(-112, 121);
$s11->movePenTo(-120, -102);
$s11->drawLine(120, 117);
$s11->drawLine(112, -120);
$s11->setLeftFill();
$s11->setRightFill();
$s11->setLine(0,0,0,0);
$s11->movePenTo(120, 102);
$s11->setLine(60, 0x00, 0x00, 0x00);
$s11->drawLine(-120, -117);
$s11->drawLine(-112, 121);
$s11->movePenTo(-120, -132);
$s11->drawLine(120, 117);
$s11->drawLine(112, -120);

### Shape 12 ###
$s12 = new SWF::Shape();
$s12->movePenTo(228, -228);
$s12->setRightFill(0x99, 0x99, 0x99);
$s12->setLine(20, 0x00, 0x00, 0x00);
$s12->drawLine(0, 456);
$s12->drawLine(-456, 0);
$s12->setLine(20, 0xcc, 0xcc, 0xcc);
$s12->drawLine(0, -456);
$s12->drawLine(456, 0);
$s12->setLeftFill();
$s12->setRightFill();
$s12->setLine(0,0,0,0);
$s12->movePenTo(120, 132);
$s12->setLine(60, 0xcc, 0xcc, 0xcc);
$s12->drawLine(-120, -117);
$s12->drawLine(-112, 121);
$s12->movePenTo(-120, -102);
$s12->drawLine(120, 117);
$s12->drawLine(112, -120);
$s12->setLeftFill();
$s12->setRightFill();
$s12->setLine(0,0,0,0);
$s12->movePenTo(120, 102);
$s12->setLine(60, 0x00, 0x00, 0x00);
$s12->drawLine(-120, -117);
$s12->drawLine(-112, 121);
$s12->movePenTo(-120, -132);
$s12->drawLine(120, 117);
$s12->drawLine(112, -120);

### Shape 13 ###
$s13 = new SWF::Shape();
$s13->movePenTo(228, -228);
$s13->setRightFill(0x99, 0x99, 0x99);
$s13->setLine(20, 0x00, 0x00, 0x00);
$s13->drawLine(0, 456);
$s13->drawLine(-456, 0);
$s13->setLine(20, 0xcc, 0xcc, 0xcc);
$s13->drawLine(0, -456);
$s13->drawLine(456, 0);
$s13->setLeftFill();
$s13->setRightFill();
$s13->setLine(0,0,0,0);
$s13->movePenTo(120, 132);
$s13->setLine(60, 0xcc, 0xcc, 0xcc);
$s13->drawLine(-120, -117);
$s13->drawLine(-112, 121);
$s13->movePenTo(-120, -102);
$s13->drawLine(120, 117);
$s13->drawLine(112, -120);
$s13->setLeftFill();
$s13->setRightFill();
$s13->setLine(0,0,0,0);
$s13->movePenTo(122, 120);
$s13->setLine(60, 0x00, 0x00, 0x00);
$s13->drawLine(-120, -117);
$s13->drawLine(-112, 121);
$s13->movePenTo(-118, -114);
$s13->drawLine(120, 117);
$s13->drawLine(112, -120);

### Button2 14 ###
$s14 = new SWF::Button();
$s14->addShape($s11, SWFBUTTON_UP);
$s14->addShape($s12, SWFBUTTON_OVER);
$s14->addShape($s13, SWFBUTTON_DOWN);
$s14->addShape($s13, SWFBUTTON_HIT);
$a = new SWF::Action("
_root.detail._visible = false;
this._visible = false;
_root.superdetails._visible = false;

");
$s14->addAction($a, SWFBUTTON_MOUSEUP);

$j2 = $detail_window->add($s1);
$j2->scaleTo(0.950256, 0.391327);
$j2->moveTo(2036, 737);
$j3 = $detail_window->add($s3);
$j3->moveTo(163, 37);
$j4 = $detail_window->add($s5);
$j4->moveTo(207, 523);
$j5 = $detail_window->add($s6);
$j5->moveTo(207, 979);
$j6 = $detail_window->add($s7);
$j6->moveTo(1402, 526);
$j7 = $detail_window->add($s8);
$j7->moveTo(1404, 995);
$j8 = $detail_window->add($s10);
$j8->scaleTo(0.800003);
$j8->moveTo(3746, 1267);
$j11 = $detail_window->add($s14);
$j11->scaleTo(0.561401);
$j11->moveTo(3720, 180);
$detail_window->nextFrame();  # end of clip frame 1 

$i1=$movie->add($detail_window);
$i1->scaleTo(0.5);
$i1->setName("detail");



#  Superdetail window
$superdetails = new SWF::Sprite();
$s1 = new SWF::Shape();
$g = new SWF::Gradient();
$g->addEntry(0.000000, 0xff, 0xff, 0xff);
$g->addEntry(0.015686, 0xe2, 0xe2, 0xe2);
$g->addEntry(0.964706, 0x9d, 0x9d, 0x9d);
$g->addEntry(1.000000, 0x5a, 0x5a, 0x5a);
$f2 = $s1->addFill($g, SWFFILL_LINEAR_GRADIENT);
$f2->scaleTo(0.25, 0.25);
$f2->moveTo(-35, 37);
$s1->movePenTo(2074, -1915);
$s1->setLeftFill(0x66, 0x66, 0x66, 0x39);
$s1->setLine(20, 0xc5, 0xc5, 0xc5, 0x39);
$s1->drawCurve(-29, -31, -41, 0);
$s1->drawLine(-3939, 0);
$s1->drawCurve(-41, 0, -30, 31);
$s1->drawCurve(-29, 32, 0, 44);
$s1->drawLine(0, 3945);
$s1->drawCurve(0, 44, 29, 31);
$s1->drawCurve(30, 33, 41, 0);
$s1->drawLine(3939, 0);
$s1->drawCurve(41, 0, 29, -33);
$s1->drawCurve(30, -31, 0, -44);
$s1->drawLine(0, -3945);
$s1->drawCurve(0, -44, -30, -32);
$s1->setLeftFill();
$s1->setRightFill();
$s1->setLine(0,0,0,0);
$s1->movePenTo(2005, -2023);
$s1->setLeftFill($f2);
$s1->setLine(20, 0x66, 0x66, 0x66);
$s1->drawCurve(-29, -31, -41, 0);
$s1->drawLine(-3939, 0);
$s1->drawCurve(-41, 0, -30, 31);
$s1->drawCurve(-29, 33, 0, 44);
$s1->drawLine(0, 3966);
$s1->drawCurve(0, 44, 29, 32);
$s1->drawCurve(30, 32, 41, 0);
$s1->drawLine(3939, 0);
$s1->drawCurve(41, 0, 29, -32);
$s1->drawCurve(30, -32, 0, -44);
$s1->drawLine(0, -3966);
$s1->drawCurve(0, -44, -30, -33);

$s3 = new SWF::TextField(SWFTEXTFIELD_NOEDIT | SWFTEXTFIELD_MULTILINE | SWFTEXTFIELD_USEFONT );
$s3->setBounds(4939, 4740);
$s3->setFont($font_general);
$s3->setHeight(260);
$s3->setColor(0x00, 0x00, 0x00, 0xff);
$s3->align(SWFTEXTFIELD_ALIGN_LEFT);
$s3->setName('texto');

### Shape 4 ###
$s4 = new SWF::Shape();
$s4->movePenTo(228, -228);
$s4->setRightFill(0x99, 0x99, 0x99);
$s4->setLine(20, 0x00, 0x00, 0x00);
$s4->drawLine(0, 456);
$s4->drawLine(-456, 0);
$s4->setLine(20, 0xcc, 0xcc, 0xcc);
$s4->drawLine(0, -456);
$s4->drawLine(456, 0);
$s4->setLeftFill();
$s4->setRightFill();
$s4->setLine(0,0,0,0);
$s4->movePenTo(120, 132);
$s4->setLine(60, 0xcc, 0xcc, 0xcc);
$s4->drawLine(-120, -117);
$s4->drawLine(-112, 121);
$s4->movePenTo(-120, -102);
$s4->drawLine(120, 117);
$s4->drawLine(112, -120);
$s4->setLeftFill();
$s4->setRightFill();
$s4->setLine(0,0,0,0);
$s4->movePenTo(120, 102);
$s4->setLine(60, 0x00, 0x00, 0x00);
$s4->drawLine(-120, -117);
$s4->drawLine(-112, 121);
$s4->movePenTo(-120, -132);
$s4->drawLine(120, 117);
$s4->drawLine(112, -120);

### Shape 5 ###
$s5 = new SWF::Shape();
$s5->movePenTo(228, -228);
$s5->setRightFill(0x99, 0x99, 0x99);
$s5->setLine(20, 0x00, 0x00, 0x00);
$s5->drawLine(0, 456);
$s5->drawLine(-456, 0);
$s5->setLine(20, 0xcc, 0xcc, 0xcc);
$s5->drawLine(0, -456);
$s5->drawLine(456, 0);
$s5->setLeftFill();
$s5->setRightFill();
$s5->setLine(0,0,0,0);
$s5->movePenTo(120, 132);
$s5->setLine(60, 0xcc, 0xcc, 0xcc);
$s5->drawLine(-120, -117);
$s5->drawLine(-112, 121);
$s5->movePenTo(-120, -102);
$s5->drawLine(120, 117);
$s5->drawLine(112, -120);
$s5->setLeftFill();
$s5->setRightFill();
$s5->setLine(0,0,0,0);
$s5->movePenTo(120, 102);
$s5->setLine(60, 0x00, 0x00, 0x00);
$s5->drawLine(-120, -117);
$s5->drawLine(-112, 121);
$s5->movePenTo(-120, -132);
$s5->drawLine(120, 117);
$s5->drawLine(112, -120);

### Shape 6 ###
$s6 = new SWF::Shape();
$s6->movePenTo(228, -228);
$s6->setRightFill(0x99, 0x99, 0x99);
$s6->setLine(20, 0x00, 0x00, 0x00);
$s6->drawLine(0, 456);
$s6->drawLine(-456, 0);
$s6->setLine(20, 0xcc, 0xcc, 0xcc);
$s6->drawLine(0, -456);
$s6->drawLine(456, 0);
$s6->setLeftFill();
$s6->setRightFill();
$s6->setLine(0,0,0,0);
$s6->movePenTo(120, 132);
$s6->setLine(60, 0xcc, 0xcc, 0xcc);
$s6->drawLine(-120, -117);
$s6->drawLine(-112, 121);
$s6->movePenTo(-120, -102);
$s6->drawLine(120, 117);
$s6->drawLine(112, -120);
$s6->setLeftFill();
$s6->setRightFill();
$s6->setLine(0,0,0,0);
$s6->movePenTo(122, 120);
$s6->setLine(60, 0x00, 0x00, 0x00);
$s6->drawLine(-120, -117);
$s6->drawLine(-112, 121);
$s6->movePenTo(-118, -114);
$s6->drawLine(120, 117);
$s6->drawLine(112, -120);

### Button2 7 ###
$s7 = new SWF::Button();
$s7->addShape($s4, SWFBUTTON_UP);
$s7->addShape($s5, SWFBUTTON_OVER);
$s7->addShape($s6, SWFBUTTON_DOWN);
$s7->addShape($s6, SWFBUTTON_HIT);
$a = new SWF::Action("
this._visible = false;
_root.detail._visible = false;

");
$s7->addAction($a, SWFBUTTON_MOUSEUP);


$s8 = new SWF::Shape();
$s8->movePenTo(228, -228);
$s8->setRightFill(0xdd, 0xdd, 0xdd);
$s8->setLine(40, 0xdd, 0xdd, 0xdd);
$s8->drawLine(-1640, 0);
$s8->setLine(40, 0x00, 0x00, 0x00);
$s8->drawLine(0, -600);
$s8->setLine(40, 0x00, 0x00, 0x00);
$s8->drawLine(1640, 0);
$s8->setLine(40, 0x00, 0x00, 0x00);
$s8->drawLine(0, 600);

$s8b = new SWF::Shape();
$s8b->movePenTo(228, -228);
$s8b->setRightFill(0xdd, 0xdd, 0xdd);
$s8b->setLine(40, 0x00, 0x00, 0x00);
$s8b->drawLine(-1640, 0);
$s8b->setLine(40, 0x00, 0x00, 0x00);
$s8b->drawLine(0, -600);
$s8b->setLine(40, 0x00, 0x00, 0x00);
$s8b->drawLine(1640, 0);
$s8b->setLine(40, 0x00, 0x00, 0x00);
$s8b->drawLine(0, 600);

$s8a = new SWF::Action("
stop();
onRelease = function() {
	var tab = this._name.substring(3,4);
	if(tab == 1) {
		gotoAndStop(1);
		_parent.tab2.gotoAndStop(2);
		_parent.texto = _global.superdetailstexttab1;
	} else {
		gotoAndStop(1);
		_parent.tab1.gotoAndStop(2);
		_parent.texto = _global.superdetailstexttab2;
	}
};

");


$fs3 = new SWF::TextField(SWFTEXTFIELD_NOEDIT | SWFTEXTFIELD_NOSELECT | SWFTEXTFIELD_USEFONT );
$fs3->setBounds(939, 200);
$fs3->setName('titlefs3');
$fs3->setFont($font_general);
$fs3->setHeight(220);
$fs3->setColor(0x00, 0x00, 0x00, 0xff);
$fs3->align(SWFTEXTFIELD_ALIGN_LEFT);
$fs3->addString('Queue');

$fs2 = new SWF::TextField(SWFTEXTFIELD_NOEDIT | SWFTEXTFIELD_NOSELECT | SWFTEXTFIELD_USEFONT );
$fs2->setBounds(939, 200);
$fs2->setName('titlefs2');
$fs2->setFont($font_general);
$fs2->setHeight(220);
$fs2->setColor(0x00, 0x00, 0x00, 0xff);
$fs2->align(SWFTEXTFIELD_ALIGN_LEFT);
$fs2->addString('Call');

$sptab = new SWF::Sprite();
$sptab->add($s8);
$sptab->add($s8a);
$sptab->nextFrame();
$sptab->add($s8b);
$sptab->add($s8a);
$sptab->nextFrame();


$s9 = new SWF::Shape();
$s9->movePenTo(1, 1);
$s9->setRightFill(0xdd, 0xdd, 0xdd);
$s9->setLine(40, 0x00, 0x00, 0x00);
$s9->drawLine(-8000, 0);
$s9->setLine(40, 0x00, 0x00, 0x00);
$s9->drawLine(0, -7700);
$s9->setLine(40, 0x00, 0x00, 0x00);
$s9->drawLine(8000, 0);
$s9->setLine(40, 0x00, 0x00, 0x00);
$s9->drawLine(0, 7700);




$j2 = $superdetails->add($s1);
$j2->scaleTo(1.349503, 1.391388);

$j5 = $superdetails->add($s9);
$j5->scaleTo(0.657898);
$j5->moveTo(2565, 2700);

$j5 = $superdetails->add($sptab);
$j5->scaleTo(0.657898);
$j5->moveTo(-1740, -2214);
$j5->setName("tab1");

$j5 = $superdetails->add($sptab);
$j5->scaleTo(0.657898);
$j5->moveTo(-620, -2214);
$j5->setName("tab2");

$j3 = $superdetails->add($s3);
$j3->moveTo(-2540, -2200);
$j3->setName('textos');

$j4 = $superdetails->add($s7);
$j4->scaleTo(0.657898);
$j4->moveTo(2472, -2610);

$ia = $superdetails->add($fs2);
$ia->moveTo(-2580,-2680);

$ia = $superdetails->add($fs3);
$ia->moveTo(-1460,-2680);

$superdetails->nextFrame();  # end of clip frame 1 

$i1 = $movie->add($superdetails);
$i1->scaleTo(0.5);
$i1->setName("superdetails");
$i1->moveTo(4900,3000);



$ventana_debug = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(120, -120);
$s1->setRightFill(0x99, 0xcc, 0xcc, 0x03);
$s1->drawLine(0, 240);
$s1->drawLine(-240, 0);
$s1->drawLine(0, -240);
$s1->drawLine(240, 0);

### Button2 3 ###
$s3 = new SWF::Button();
$s3->addShape($s1, SWFBUTTON_HIT | SWFBUTTON_DOWN | SWFBUTTON_OVER | SWFBUTTON_UP);
$a = new SWF::Action("
beginDrag(this, 0);
var este = this.getDepth();
var aquel = _level0.codebox.getDepth();

if(aquel > este)
{
  swapDepths(_level0.codebox);
  ;
}

");
$s3->addAction($a, SWFBUTTON_MOUSEDOWN);
$a = new SWF::Action("
endDrag();

");
$s3->addAction($a, SWFBUTTON_MOUSEUPOUTSIDE | SWFBUTTON_MOUSEUP);

### Shape 4 ###
$s4 = new SWF::Shape();
$g = new SWF::Gradient();
$g->addEntry(0.000000, 0xff, 0xff, 0xff);
$g->addEntry(0.015686, 0xe2, 0xe2, 0xe2);
$g->addEntry(0.964706, 0x9d, 0x9d, 0x9d);
$g->addEntry(1.000000, 0x5a, 0x5a, 0x5a);
$f2 = $s4->addFill($g, SWFFILL_LINEAR_GRADIENT);
$f2->scaleTo(0.25, 0.25);
$f2->moveTo(-35, 37);
$s4->movePenTo(2074, -1915);
$s4->setLeftFill(0x66, 0x66, 0x66, 0x39);
$s4->setLine(20, 0xc5, 0xc5, 0xc5, 0x39);
$s4->drawCurve(-29, -31, -41, 0);
$s4->drawLine(-3939, 0);
$s4->drawCurve(-41, 0, -30, 31);
$s4->drawCurve(-29, 32, 0, 44);
$s4->drawLine(0, 3945);
$s4->drawCurve(0, 44, 29, 31);
$s4->drawCurve(30, 33, 41, 0);
$s4->drawLine(3939, 0);
$s4->drawCurve(41, 0, 29, -33);
$s4->drawCurve(30, -31, 0, -44);
$s4->drawLine(0, -3945);
$s4->drawCurve(0, -44, -30, -32);
$s4->setLeftFill();
$s4->setRightFill();
$s4->setLine(0,0,0,0);
$s4->movePenTo(2005, -2023);
$s4->setLeftFill($f2);
$s4->setLine(20, 0x66, 0x66, 0x66);
$s4->drawCurve(-29, -31, -41, 0);
$s4->drawLine(-3939, 0);
$s4->drawCurve(-41, 0, -30, 31);
$s4->drawCurve(-29, 33, 0, 44);
$s4->drawLine(0, 3966);
$s4->drawCurve(0, 44, 29, 32);
$s4->drawCurve(30, 32, 41, 0);
$s4->drawLine(3939, 0);
$s4->drawCurve(41, 0, 29, -32);
$s4->drawCurve(30, -32, 0, -44);
$s4->drawLine(0, -3966);
$s4->drawCurve(0, -44, -30, -33);

$s6 = new SWF::TextField(SWFTEXTFIELD_NOEDIT | SWFTEXTFIELD_NOSELECT | SWFTEXTFIELD_USEFONT );
$s6->setBounds(8104, 398);
$s6->setFont($font_general);
$s6->setHeight(320);
$s6->setColor(0x00, 0x00, 0x00, 0xff);
$s6->align(SWFTEXTFIELD_ALIGN_LEFT);
$s6->setName("title");
$s6->addString('Debug Window');

### Shape 7 ###
$s7 = new SWF::Shape();
$s7->movePenTo(228, -228);
$s7->setRightFill(0x99, 0x99, 0x99);
$s7->setLine(20, 0x00, 0x00, 0x00);
$s7->drawLine(0, 456);
$s7->drawLine(-456, 0);
$s7->setLine(20, 0xcc, 0xcc, 0xcc);
$s7->drawLine(0, -456);
$s7->drawLine(456, 0);
$s7->setLeftFill();
$s7->setRightFill();
$s7->setLine(0,0,0,0);
$s7->movePenTo(120, 132);
$s7->setLine(60, 0xcc, 0xcc, 0xcc);
$s7->drawLine(-120, -117);
$s7->drawLine(-112, 121);
$s7->movePenTo(-120, -102);
$s7->drawLine(120, 117);
$s7->drawLine(112, -120);
$s7->setLeftFill();
$s7->setRightFill();
$s7->setLine(0,0,0,0);
$s7->movePenTo(120, 102);
$s7->setLine(60, 0x00, 0x00, 0x00);
$s7->drawLine(-120, -117);
$s7->drawLine(-112, 121);
$s7->movePenTo(-120, -132);
$s7->drawLine(120, 117);
$s7->drawLine(112, -120);

### Shape 8 ###
$s8 = new SWF::Shape();
$s8->movePenTo(228, -228);
$s8->setRightFill(0x99, 0x99, 0x99);
$s8->setLine(20, 0x00, 0x00, 0x00);
$s8->drawLine(0, 456);
$s8->drawLine(-456, 0);
$s8->setLine(20, 0xcc, 0xcc, 0xcc);
$s8->drawLine(0, -456);
$s8->drawLine(456, 0);
$s8->setLeftFill();
$s8->setRightFill();
$s8->setLine(0,0,0,0);
$s8->movePenTo(120, 132);
$s8->setLine(60, 0xcc, 0xcc, 0xcc);
$s8->drawLine(-120, -117);
$s8->drawLine(-112, 121);
$s8->movePenTo(-120, -102);
$s8->drawLine(120, 117);
$s8->drawLine(112, -120);
$s8->setLeftFill();
$s8->setRightFill();
$s8->setLine(0,0,0,0);
$s8->movePenTo(120, 102);
$s8->setLine(60, 0x00, 0x00, 0x00);
$s8->drawLine(-120, -117);
$s8->drawLine(-112, 121);
$s8->movePenTo(-120, -132);
$s8->drawLine(120, 117);
$s8->drawLine(112, -120);

### Shape 9 ###
$s9 = new SWF::Shape();
$s9->movePenTo(228, -228);
$s9->setRightFill(0x99, 0x99, 0x99);
$s9->setLine(20, 0x00, 0x00, 0x00);
$s9->drawLine(0, 456);
$s9->drawLine(-456, 0);
$s9->setLine(20, 0xcc, 0xcc, 0xcc);
$s9->drawLine(0, -456);
$s9->drawLine(456, 0);
$s9->setLeftFill();
$s9->setRightFill();
$s9->setLine(0,0,0,0);
$s9->movePenTo(120, 132);
$s9->setLine(60, 0xcc, 0xcc, 0xcc);
$s9->drawLine(-120, -117);
$s9->drawLine(-112, 121);
$s9->movePenTo(-120, -102);
$s9->drawLine(120, 117);
$s9->drawLine(112, -120);
$s9->setLeftFill();
$s9->setRightFill();
$s9->setLine(0,0,0,0);
$s9->movePenTo(122, 120);
$s9->setLine(60, 0x00, 0x00, 0x00);
$s9->drawLine(-120, -117);
$s9->drawLine(-112, 121);
$s9->movePenTo(-118, -114);
$s9->drawLine(120, 117);
$s9->drawLine(112, -120);

### Button2 10 ###
$s10 = new SWF::Button();
$s10->addShape($s7, SWFBUTTON_UP);
$s10->addShape($s8, SWFBUTTON_OVER);
$s10->addShape($s9, SWFBUTTON_DOWN);
$s10->addShape($s9, SWFBUTTON_HIT);
$a = new SWF::Action("
this._visible = false;

");
$s10->addAction($a, SWFBUTTON_MOUSEUP);

### Shape 11 ###
$s11 = new SWF::Shape();
$s11->movePenTo(3825, -1782);
$s11->setRightFill(0xf0, 0xf0, 0xf0);
$s11->setLine(20, 0x66, 0x66, 0x66);
$s11->drawLine(0, 3620);
$s11->drawLine(-8158, 0);
$s11->drawLine(0, -3620);
$s11->drawLine(8158, 0);

$s13 = new SWF::TextField(SWFTEXTFIELD_NOEDIT | SWFTEXTFIELD_MULTILINE | SWFTEXTFIELD_USEFONT );
$s13->setBounds(8000, 3606);
$s13->setFont($font_general);
$s13->setHeight(320);
$s13->setColor(0x00, 0x00, 0x00, 0xff);
$s13->align(SWFTEXTFIELD_ALIGN_LEFT);
$s13->setRightMargin(80);
$s13->setIndentation(40);
$s13->setName('logcontent');

### MovieClip 14 ###
$j2 = $ventana_debug->add($s4);
$j2->scaleTo(2.121078, 1.087326);
$j2->moveTo(0, -87);
$j3 = $ventana_debug->add($s3);
$j3->scaleTo(34.419983, 1.277756);
$j3->moveTo(-233, -2030);
$j5 = $ventana_debug->add($s6);
$j5->moveTo(-4323, -2220);
$j5->setName('title');
$j6 = $ventana_debug->add($s10);
$j6->scaleTo(0.657898);
$j6->moveTo(4015, -2031);
$ventana_debug->add($s11);
$j11 = $ventana_debug->add($s13);
#$j11->scaleTo(1.289948, 0.945129); 
#$j11->scaleTo(1.2, 0.9);
$j11->moveTo(-4281, -1710);
$j11->setName('Field1');
$ventana_debug->nextFrame();  # end of ventana_debug 

####################################################


$i1=$movie->add($ventana_debug);
$i1->scaleTo(0.5);
$i1->setDepth(100);
$i1->moveTo(5000,2500);
$i1->setName("log");





$ledcolor = new SWF::Sprite();
$s1 = new SWF::Shape();
$s1->movePenTo(524, 125);
$s1->setLeftFill(255, 0x00, 0x00);
$s1->drawCurve(-112, -86, -125, 0);
$s1->drawCurve(-127, 0, -66, 86);
$s1->drawLine(-21, 33);
$s1->drawCurve(-38, 76, 27, 99);
$s1->drawLine(11, 35);
$s1->drawCurve(37, 99, 96, 73);
$s1->drawLine(13, 10);
$s1->drawCurve(107, 75, 118, 1);
$s1->drawCurve(118, -1, 65, -75);
$s1->drawLine(9, -10);
$s1->drawCurve(66, -86, -33, -121);
$s1->drawCurve(-33, -122, -112, -86);
$i1 = $ledcolor->add($s1);
$i1->scaleTo(0.5);
$i1->moveTo(-185, -174);
$ledcolor->nextFrame();  # end of frame 1


$ledsombra = new SWF::Sprite();
$s2 = new SWF::Shape();
$s2->movePenTo(216, -124);
$s2->setRightFill(0x00, 0x00, 0x00, 0x32);
$s2->drawCurve(30, 58, -21, 78);
$s2->drawLine(-8, 25);
$s2->drawCurve(-26, 71, -68, 52);
$s2->drawLine(-1, 1);
$s2->drawLine(-8, 6);
$s2->drawCurve(-77, 54, -84, 0);
$s2->drawCurve(-94, -1, -50, -60);
$s2->drawLine(-6, -6);
$s2->drawLine(0, -2);
$s2->drawCurve(-54, -68, 25, -95);
$s2->drawCurve(23, -87, 80, -62);
$s2->drawCurve(80, -62, 90, 1);
$s2->drawCurve(99, -1, 52, 70);
$s2->drawLine(0, 1);
$s2->drawLine(18, 27);
$s2->movePenTo(191, -146);
$s2->setLeftFill(0x00, 0x00, 0x00, 0x32);
$s2->setRightFill(0x00, 0x00, 0x00, 0x65);
$s2->drawLine(16, 25);
$s2->drawCurve(29, 57, -20, 74);
$s2->drawLine(-7, 24);
$s2->drawCurve(-26, 68, -66, 51);
$s2->drawLine(0, 1);
$s2->drawCurve(-5, 2, -4, 4);
$s2->drawCurve(-73, 52, -82, 0);
$s2->drawCurve(-89, -1, -49, -57);
$s2->drawLine(-4, -7);
$s2->drawLine(-1, -1);
$s2->drawCurve(-52, -64, 25, -91);
$s2->drawCurve(22, -85, 78, -59);
$s2->drawCurve(77, -60, 87, 1);
$s2->drawCurve(95, -1, 49, 66);
$s2->drawLine(0, 1);
$s2->movePenTo(184, -141);
$s2->setLeftFill(0x00, 0x00, 0x00, 0x65);
$s2->setRightFill(0x00, 0x00, 0x00, 0x98);
$s2->drawLine(15, 25);
$s2->drawCurve(28, 53, -19, 71);
$s2->drawLine(-8, 23);
$s2->drawLine(0, -1);
$s2->drawCurve(-24, 67, -64, 49);
$s2->drawLine(-9, 7);
$s2->drawCurve(-71, 50, -79, 0);
$s2->drawCurve(-85, -1, -46, -54);
$s2->drawLine(-5, -6);
$s2->drawLine(-1, -1);
$s2->drawCurve(-48, -61, 24, -87);
$s2->drawCurve(21, -81, 75, -58);
$s2->drawCurve(75, -58, 84, 1);
$s2->drawCurve(89, -1, 48, 62);
$s2->drawLine(0, 1);
$s2->movePenTo(177, -135);
$s2->setLeftFill(0x00, 0x00, 0x00, 0x98);
$s2->setRightFill(0x00, 0x00, 0x00, 0xcb);
$s2->drawLine(14, 22);
$s2->drawCurve(26, 52, -18, 66);
$s2->drawLine(-7, 23);
$s2->drawCurve(-24, 63, -61, 48);
$s2->drawLine(-9, 6);
$s2->drawCurve(-69, 49, -76, 0);
$s2->drawCurve(-80, -1, -43, -51);
$s2->drawLine(-6, -6);
$s2->drawLine(-1, 0);
$s2->drawCurve(-45, -59, 22, -81);
$s2->drawCurve(21, -79, 73, -56);
$s2->drawCurve(72, -56, 81, 1);
$s2->drawCurve(85, -1, 45, 59);
$s2->drawLine(0, 1);
$s2->movePenTo(183, -108);
$s2->setLeftFill(0x00, 0x00, 0x00, 0xcb);
$s2->setRightFill(0x00, 0x00, 0x00);
$s2->drawCurve(24, 47, -17, 63);
$s2->drawLine(-6, 22);
$s2->drawCurve(-23, 62, -60, 46);
$s2->drawLine(-8, 5);
$s2->drawCurve(-66, 48, -74, 0);
$s2->drawCurve(-76, 0, -41, -49);
$s2->drawLine(-5, -6);
$s2->drawLine(-1, 0);
$s2->drawCurve(-42, -55, 21, -77);
$s2->drawCurve(21, -77, 70, -53);
$s2->drawCurve(69, -54, 78, 1);
$s2->drawCurve(80, -1, 43, 56);
$s2->drawLine(13, 22);
$i2 = $ledsombra->add($s2);
#$i2->scaleTo(1.574982, -1.574982);
$i2->scaleTo(0.72, -0.72);
$i2->rotateTo(-180);
#$i2->moveTo(369, 348);
#$i2->moveTo(185, 174);
$i2->multColor(1.000000, 1.000000, 1.000000, 0.351562);
$i2->addColor(0x00, 0x00, 0x00);
$ledsombra->nextFrame();

$ledbrillo = new SWF::Sprite();
$s4 = new SWF::Shape();
$g = new SWF::Gradient();
$g->addEntry(0.368627, 0x00, 0x00, 0x00, 0x00);
$g->addEntry(0.913725, 0x00, 0x00, 0x00, 0x99);
$f1 = $s4->addFill($g, SWFFILL_RADIAL_GRADIENT);
$f1->skewXTo(0.234316);
$f1->scaleTo(0.06, -0.06);
$f1->rotateTo(165.488144);
$f1->moveTo(383, 415);
$g = new SWF::Gradient();
$g->addEntry(0.000000, 0xff, 0xff, 0xff);
$g->addEntry(0.282353, 0xff, 0xff, 0xff, 0x8d);
$g->addEntry(1.000000, 0xff, 0xff, 0xff, 0x00);
$f2 = $s4->addFill($g, SWFFILL_RADIAL_GRADIENT);
$f2->skewXTo(0.234808);
$f2->scaleTo(0.030644, -0.027754);
$f2->rotateTo(165.465546);
$f2->moveTo(405, 500);
$g = new SWF::Gradient();
$g->addEntry(0.196078, 0xff, 0xff, 0xff);
$g->addEntry(0.921569, 0xff, 0xff, 0xff, 0x00);
$f3 = $s4->addFill($g, SWFFILL_RADIAL_GRADIENT);
$f3->skewXTo(0.103049);
$f3->scaleTo(0.015, -0.009);
$f3->rotateTo(170.450867);
$f3->moveTo(291, 115);
$s4->movePenTo(669, 336);
$s4->setRightFill($f1);
$s4->drawCurve(-34, -124, -113, -85);
$s4->drawCurve(-110, -86, -126, 0);
$s4->drawCurve(-126, 0, -67, 86);
$s4->drawCurve(-66, 85, 34, 124);
$s4->drawCurve(32, 119, 112, 88);
$s4->drawCurve(113, 85, 126, 0);
$s4->drawCurve(126, 0, 65, -85);
$s4->drawCurve(65, -88, -31, -119);
$s4->setLeftFill();
$s4->setRightFill();
#brillo grande inferior
$s4->movePenTo(286, 41);
$s4->setRightFill($f2);
$s4->drawCurve(-126, -2, -65, 88);
$s4->drawCurve(-65, 85, 31, 122);
$s4->drawCurve(34, 121, 110, 86);
$s4->drawCurve(113, 85, 126, 0);
$s4->drawCurve(126, 0, 65, -85);
$s4->drawCurve(67, -86, -33, -121);
$s4->drawCurve(-32, -122, -112, -85);
$s4->drawCurve(-113, -88, -126, 2);
$s4->setLeftFill();
$s4->setRightFill();
# brillo superior
$s4->movePenTo(184, 57);
$s4->setRightFill($f3);
$s4->drawCurve(-32, 24, 9, 34);
$s4->drawCurve(9, 34, 45, 23);
$s4->drawCurve(45, 25, 53, 0);
$s4->drawCurve(54, 0, 32, -25);
$s4->drawCurve(32, -23, -10, -34);
$s4->drawCurve(-8, -34, -45, -24);
$s4->drawCurve(-45, -24, -54, 0);
$s4->drawCurve(-53, 0, -32, 24);
$i6 = $ledbrillo->add($s4);
$i6->scaleTo(0.5);
$i6->moveTo(-185, -174);
$ledbrillo->nextFrame();  


# Icons movieclips

####### Arrow

my $fle = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(5642, 3859);
$s1->setRightFill(0x33, 0x33, 0x33);
$s1->drawCurve(58, 58, 0, 83);
$s1->drawCurve(0, 83, -58, 59);
$s1->drawCurve(-59, 58, -83, 0);
$s1->drawCurve(-83, 0, -58, -58);
$s1->drawCurve(-59, -59, 0, -83);
$s1->drawCurve(0, -83, 59, -58);
$s1->drawCurve(58, -59, 83, 0);
$s1->drawCurve(83, 0, 59, 59);
$s1->movePenTo(5543, 3930);
$s1->setLeftFill(0x33, 0x33, 0x33);
$s1->setRightFill(0xff, 0xff, 0xff);
$s1->drawLine(66, 0);
$s1->drawLine(0, 144);
$s1->drawLine(-66, -1);
$s1->drawLine(0, 65);
$s1->drawLine(-191, -136);
$s1->drawLine(191, -139);
$s1->drawLine(0, 67);
$i1 = $fle->add($s1);
$i1->moveTo(-2750,-2000);
$i1->scaleTo(0.5);
$fle->nextFrame();  # end of frame 1
$fle->remove($i1);

### Shape 2 ###
$s2 = new SWF::Shape();
$s2->movePenTo(5500, 3800);
$s2->setRightFill(0x33, 0x33, 0x33);
$s2->drawCurve(83, 0, 59, 59);
$s2->drawCurve(58, 58, 0, 83);
$s2->drawCurve(0, 83, -58, 59);
$s2->drawCurve(-59, 58, -83, 0);
$s2->drawCurve(-83, 0, -58, -58);
$s2->drawCurve(-59, -59, 0, -83);
$s2->drawCurve(0, -83, 59, -58);
$s2->drawCurve(58, -59, 83, 0);
$s2->movePenTo(5457, 3927);
$s2->setLeftFill(0x33, 0x33, 0x33);
$s2->setRightFill(0xff, 0xff, 0xff);
$s2->drawLine(0, -64);
$s2->drawLine(192, 136);
$s2->drawLine(-192, 139);
$s2->drawLine(0, -67);
$s2->drawLine(-66, -1);
$s2->drawLine(0, -143);
$s2->drawLine(66, 0);
$i1 = $fle->add($s2);
$i1->moveTo(-2750,-2000);
$i1->scaleTo(0.5);
$fle->nextFrame();  # end of frame 2
$fle->remove($i1);

### Shape 3 ###
$s3 = new SWF::Shape();
$s3->movePenTo(5500, 3800);
$s3->setRightFill(0x33, 0x33, 0x33);
$s3->drawCurve(83, 0, 59, 59);
$s3->drawCurve(58, 58, 0, 83);
$s3->drawCurve(0, 83, -58, 59);
$s3->drawCurve(-59, 58, -83, 0);
$s3->drawCurve(-83, 0, -58, -58);
$s3->drawCurve(-59, -59, 0, -83);
$s3->drawCurve(0, -83, 59, -58);
$s3->drawCurve(58, -59, 83, 0);
$s3->movePenTo(5574, 3891);
$s3->setLeftFill(0x33, 0x33, 0x33);
$s3->setRightFill(0xff, 0xff, 0xff);
$s3->drawLine(-1, 66);
$s3->drawLine(65, 0);
$s3->drawLine(-136, 192);
$s3->drawLine(-139, -192);
$s3->drawLine(67, 0);
$s3->drawLine(0, -66);
$s3->drawLine(144, 0);
$i1 = $fle->add($s3);
$i1->moveTo(-2750,-2000);
$i1->scaleTo(0.5);
$fle->nextFrame();  # end of frame 3
$fle->remove($i1);






# Icon 3 Shape
my $s_icon2 = new SWF::Shape();
$s_icon2->movePenTo(3339, 2660);
$s_icon2->setRightFill(0xcc, 0xcc, 0xcc, 0x00);
$s_icon2->drawLine(-6639, 0);
$s_icon2->drawLine(0,     -5340);
$s_icon2->drawLine(6639,  0);
$s_icon2->drawLine(0,     5340);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(2622, -2528);
$s_icon2->setLeftFill(0x00, 0x00, 0x00);
$s_icon2->drawCurve(-129, -82, -182, -26);
$s_icon2->drawLine(-329, -25);
$s_icon2->drawCurve(-648, -45,  -912, 195);
$s_icon2->drawCurve(-744, 158,  -633, 349);
$s_icon2->drawCurve(-748, 412,  -329, 551);
$s_icon2->drawCurve(-141, 237,  -51,  287);
$s_icon2->drawCurve(-53,  294,  58,   257);
$s_icon2->drawCurve(62,   279,  180,  170);
$s_icon2->drawCurve(200,  189,  319,  26);
$s_icon2->drawCurve(216,  17,   238,  -98);
$s_icon2->drawCurve(235,  -96,  173,  -175);
$s_icon2->drawCurve(184,  -185, 55,   -214);
$s_icon2->drawCurve(61,   -239, -116, -229);
$s_icon2->drawCurve(-52,  -105, -126, -100);
$s_icon2->drawCurve(-62,  -49,  -166, -106);
$s_icon2->drawCurve(-3,   282,  -101, 178);
$s_icon2->drawCurve(-125, 220,  -251, 0);
$s_icon2->drawCurve(111,  -550, 272,  -330);
$s_icon2->drawCurve(277,  -329, 620,  -295);
$s_icon2->drawCurve(993,  -472, 807,  -4);
$s_icon2->drawCurve(-80,  192,  -228, 70);
$s_icon2->drawCurve(-106, 32,   -335, 29);
$s_icon2->drawCurve(-279, 25,   -118, 70);
$s_icon2->drawCurve(-172, 104,  -2,   258);
$s_icon2->drawCurve(-51,  5,    -120, 41);
$s_icon2->drawCurve(-110, 39,   -59,  -2);
$s_icon2->drawCurve(-69,  -4,   -107, -35);
$s_icon2->drawLine(-170, -51);
$s_icon2->drawCurve(-196, -41, -128, 171);
$s_icon2->drawCurve(-95,  129, 38,   131);
$s_icon2->drawCurve(28,   96,  120,  135);
$s_icon2->drawLine(203, 230);
$s_icon2->drawCurve(103, 137, 3,    119);
$s_icon2->drawCurve(4,   185, -112, 228);
$s_icon2->drawCurve(-69, 140, -157, 226);
$s_icon2->drawLine(-125, 142);
$s_icon2->drawCurve(-84,  96,   -4,   62);
$s_icon2->drawCurve(-2,   71,   112,  115);
$s_icon2->drawCurve(60,   62,   107,  92);
$s_icon2->drawCurve(600,  600,  500,  150);
$s_icon2->drawCurve(831,  245,  809,  -555);
$s_icon2->drawCurve(237,  -169, 36,   -34);
$s_icon2->drawCurve(152,  -145, -45,  -132);
$s_icon2->drawCurve(-160, 62,   -299, 191);
$s_icon2->drawCurve(-296, 188,  -165, 62);
$s_icon2->drawCurve(-374, 141,  -411, -83);
$s_icon2->drawCurve(-389, -79,  -326, -256);
$s_icon2->drawCurve(-108, -86,  -212, -211);
$s_icon2->drawCurve(-202, -200, -118, -89);
$s_icon2->drawLine(0, -20);
$s_icon2->drawCurve(183,  -140, 118,  -250);
$s_icon2->drawCurve(115,  -245, 11,   -262);
$s_icon2->drawCurve(12,   -278, -114, -206);
$s_icon2->drawCurve(-126, -229, -259, -89);
$s_icon2->drawCurve(62,   -182, 144,  -25);
$s_icon2->drawCurve(148,  -25,  106,  172);
$s_icon2->drawCurve(124,  -21,  161,  -65);
$s_icon2->drawCurve(187,  -75,  68,   -73);
$s_icon2->drawCurve(37,   -39,  30,   -107);
$s_icon2->drawCurve(32,   -118, 27,   -39);
$s_icon2->drawCurve(88,   -134, 149,  2);
$s_icon2->drawCurve(145,  3,    92,   126);
$s_icon2->drawCurve(154,  -44,  210,  -119);
$s_icon2->drawCurve(241,  -136, 68,   -108);
$s_icon2->drawCurve(30,   -49,  16,   -93);
$s_icon2->drawLine(21, -151);
$s_icon2->drawCurve(-666, 0,    -553, 127);
$s_icon2->drawCurve(-597, 137,  -544, 302);
$s_icon2->drawCurve(-689, 377,  -228, 437);
$s_icon2->drawCurve(-183, 355,  0,    704);
$s_icon2->drawCurve(369,  0,    209,  -219);
$s_icon2->drawCurve(181,  -192, 81,   -388);
$s_icon2->drawCurve(213,  220,  -59,  276);
$s_icon2->drawCurve(-53,  246,  -237, 183);
$s_icon2->drawCurve(-238, 184,  -270, -1);
$s_icon2->drawCurve(-302, -2,   -198, -247);
$s_icon2->drawCurve(-232, -292, 179,  -487);
$s_icon2->drawCurve(141,  -381, 296,  -316);
$s_icon2->drawCurve(363,  -387, 717,  -320);
$s_icon2->drawCurve(1330, -603, 950,  93);
$s_icon2->drawCurve(243,  22,   222,  130);
$s_icon2->drawCurve(220,  128,  130,  192);
$s_icon2->drawCurve(138,  204,  -4,   219);
$s_icon2->drawCurve(-5,   242,  -181, 217);
$s_icon2->drawCurve(-82,  99,   -151, 23);
$s_icon2->drawCurve(-138, 21,   -158, -47);
$s_icon2->drawCurve(-155, -46,  -114, -93);
$s_icon2->drawCurve(-122, -99,  -33,  -118);
$s_icon2->drawCurve(-17,  -55,  31,   -76);
$s_icon2->drawLine(56, -129);
$s_icon2->drawLine(20, 0);
$s_icon2->drawCurve(53,  265,  224,  122);
$s_icon2->drawCurve(215, 117,  268,  -64);
$s_icon2->drawCurve(-38, -177, -122, -27);
$s_icon2->drawLine(-126, -17);
$s_icon2->drawCurve(-86,  -12,  -68,  -34);
$s_icon2->drawCurve(-217, -105, -63,  -288);
$s_icon2->drawCurve(-320, 171,  54,   289);
$s_icon2->drawCurve(43,   235,  263,  245);
$s_icon2->drawCurve(-32,  -19,  -244, -117);
$s_icon2->drawCurve(-173, -83,  -85,  -75);
$s_icon2->drawCurve(-45,  -38,  -98,  -166);
$s_icon2->drawCurve(-79,  -133, -104, -36);
$s_icon2->drawCurve(-165, -56,  -79,  193);
$s_icon2->drawCurve(-32,  79,   1,    87);
$s_icon2->drawCurve(0,    89,   35,   55);
$s_icon2->drawLine(20, 0);
$s_icon2->drawCurve(26,  -54,  24, -26);
$s_icon2->drawCurve(112, -131, 75, 74);
$s_icon2->drawLine(63, 87);
$s_icon2->drawCurve(43, 67, 30, 29);
$s_icon2->drawCurve(35, 37, 81, 34);
$s_icon2->drawLine(131, 53);
$s_icon2->drawCurve(1018, 508, 332,  841);
$s_icon2->drawCurve(107,  286, 33,   129);
$s_icon2->drawCurve(69,   266, -46,  159);
$s_icon2->drawCurve(-39,  133, -133, 137);
$s_icon2->drawCurve(-61,  63,  -180, 150);
$s_icon2->drawCurve(-374, 306, -372, 148);
$s_icon2->drawCurve(-452, 179, -402, -76);
$s_icon2->drawCurve(-469, -88, -484, -377);
$s_icon2->drawLine(-405, -339);
$s_icon2->drawCurve(-254, -217, -168, -119);
$s_icon2->drawCurve(-80,  281,  251,  280);
$s_icon2->drawCurve(88,   99,   154,  125);
$s_icon2->drawLine(247, 198);
$s_icon2->drawCurve(536,  454,  708,  42);
$s_icon2->drawCurve(699,  41,   597,  -374);
$s_icon2->drawCurve(223,  -143, 283,  -294);
$s_icon2->drawCurve(357,  -370, 117,  -99);
$s_icon2->drawCurve(-95,  -688, -323, -580);
$s_icon2->drawCurve(-372, -668, -550, -263);
$s_icon2->drawLine(179, 13);
$s_icon2->drawLine(184, 26);
$s_icon2->drawCurve(224,  25,   129,  -78);
$s_icon2->drawCurve(208,  -128, 96,   -235);
$s_icon2->drawCurve(88,   -215, -23,  -261);
$s_icon2->drawCurve(-21,  -252, -117, -218);
$s_icon2->drawCurve(-121, -225, -186, -119);
$s_icon2->movePenTo(-782, -2506);
$s_icon2->drawCurve(-149, -33, -83,  -2);
$s_icon2->drawCurve(-259, -6,  -25,  266);
$s_icon2->drawCurve(-290, -76, -185, 73);
$s_icon2->drawCurve(-225, 89,  0,    294);
$s_icon2->drawCurve(-141, -25, -128, 24);
$s_icon2->drawCurve(-129, 24,  -80,  69);
$s_icon2->drawCurve(-187, 161, 165,  287);
$s_icon2->drawLine(-230, 10);
$s_icon2->drawCurve(-136, 18,  -71, 65);
$s_icon2->drawCurve(-126, 109, 28,  183);
$s_icon2->drawCurve(28,   181, 147, 74);
$s_icon2->drawLine(0, 20);
$s_icon2->drawCurve(-194, 18,  -117, 128);
$s_icon2->drawCurve(-106, 116, -3,   155);
$s_icon2->drawCurve(-2,   157, 103,  106);
$s_icon2->drawCurve(116,  118, 203,  1);
$s_icon2->drawLine(0, 20);
$s_icon2->drawCurve(-208, 97,  -38, 224);
$s_icon2->drawCurve(-40,  230, 186, 139);
$s_icon2->drawCurve(74,   54,  107, 12);
$s_icon2->drawCurve(35,   4,   164, 0);
$s_icon2->drawLine(-60, 137);
$s_icon2->drawCurve(-28,  79,   11,  64);
$s_icon2->drawCurve(29,   212,  260, 95);
$s_icon2->drawCurve(212,  77,   236, -28);
$s_icon2->drawCurve(179,  -21,  199, -113);
$s_icon2->drawCurve(41,   -23,  301, -199);
$s_icon2->drawCurve(-117, -134, -83, 14);
$s_icon2->drawCurve(-75,  15,   -83, 46);
$s_icon2->drawLine(-138, 86);
$s_icon2->drawCurve(-173, 108, -151, -15);
$s_icon2->drawCurve(-126, -12, -90,  -60);
$s_icon2->drawCurve(-86,  -57, -28,  -82);
$s_icon2->drawCurve(-30,  -85, 40,   -84);
$s_icon2->drawCurve(43,   -92, 117,  -68);
$s_icon2->drawLine(0, -20);
$s_icon2->drawCurve(-701, 0,    91,  -340);
$s_icon2->drawCurve(24,   -81,  83,  -89);
$s_icon2->drawCurve(100,  -97,  43,  -53);
$s_icon2->drawCurve(-151, -41,  -72, -29);
$s_icon2->drawCurve(-123, -49,  -64, -84);
$s_icon2->drawCurve(-149, -193, 200, -133);
$s_icon2->drawCurve(161,  -107, 218, -3);
$s_icon2->drawLine(0, -20);
$s_icon2->drawCurve(-72,  -45,  -65, -75);
$s_icon2->drawCurve(-215, -237, 221, -118);
$s_icon2->drawCurve(159,  -85,  292, 0);
$s_icon2->drawLine(-82, -164);
$s_icon2->drawCurve(-50, -100, -2,  -76);
$s_icon2->drawCurve(-2,  -184, 225, -21);
$s_icon2->drawLine(351, 25);
$s_icon2->drawCurve(-50,  -142, -4,  -38);
$s_icon2->drawCurve(-19,  -292, 267, 5);
$s_icon2->drawCurve(148,  2,    298, 85);
$s_icon2->drawCurve(-113, -272, 214, -40);
$s_icon2->drawCurve(100,  -19,  319, 51);
$s_icon2->drawLine(40,   -120);
$s_icon2->drawLine(-224, -45);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(2838, -1701);
$s_icon2->setLeftFill(0xb8, 0xb8, 0xd9);
$s_icon2->drawCurve(-9,   -387, -364, -223);
$s_icon2->drawCurve(-310, -190, -413, 0);
$s_icon2->drawCurve(-688, 0,    -717, 217);
$s_icon2->drawCurve(-699, 212,  -596, 377);
$s_icon2->drawCurve(-609, 384,  -171, 390);
$s_icon2->drawLine(20, 0);
$s_icon2->drawCurve(317,  -418, 522, -307);
$s_icon2->drawCurve(396,  -235, 625, -224);
$s_icon2->drawCurve(782,  -281, 518, -12);
$s_icon2->drawCurve(1039, -30,  257, 547);
$s_icon2->drawCurve(64,   137,  0,   323);
$s_icon2->drawCurve(33,   -55,  5,   -80);
$s_icon2->drawCurve(3,    -35,  -5,  -110);
$s_icon2->movePenTo(-598, -2341);
$s_icon2->drawCurve(-225, -62, -86, -5);
$s_icon2->drawCurve(-200, -11, -49, 138);
$s_icon2->drawLine(520, 40);
$s_icon2->drawLine(40,  -100);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(1782, -2181);
$s_icon2->setLeftFill(0xb8, 0xb8, 0xd9);
$s_icon2->drawCurve(-965, 4,   -1075, 586);
$s_icon2->drawCurve(-670, 361, -210,  489);
$s_icon2->drawLine(20, 0);
$s_icon2->drawCurve(385, -610, 887, -381);
$s_icon2->drawCurve(806, -347, 802, -2);
$s_icon2->drawLine(20, -100);
$s_icon2->movePenTo(-1669, -2135);
$s_icon2->drawCurve(-97,  52,   -32,  102);
$s_icon2->drawCurve(154,  -93,  165,  20);
$s_icon2->drawCurve(126,  15,   195,  98);
$s_icon2->drawCurve(-23,  -101, -91,  -64);
$s_icon2->drawCurve(-85,  -59,  -111, -9);
$s_icon2->drawCurve(-113, -8,   -88,  47);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(1822, -1981);
$s_icon2->setLeftFill(0xb8, 0xb8, 0xd9);
$s_icon2->drawLine(-640, 380);
$s_icon2->drawCurve(-51, -103, -88, -29);
$s_icon2->drawCurve(-79, -25,  -90, 37);
$s_icon2->drawCurve(-85, 35,   -65, 76);
$s_icon2->drawCurve(-66, 79,   -16, 90);
$s_icon2->drawLine(20, 0);
$s_icon2->drawCurve(49,  -114, 94,  -53);
$s_icon2->drawCurve(104, -59,  113, 52);
$s_icon2->drawCurve(28,  15,   55,  46);
$s_icon2->drawCurve(53,  38,   44,  -2);
$s_icon2->drawCurve(69,  -4,   117, -80);
$s_icon2->drawLine(174, -123);
$s_icon2->drawLine(171, -101);
$s_icon2->drawCurve(104, -75, -15, -80);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(-2338, -1501);
$s_icon2->setLeftFill(0xb8, 0xb8, 0xd9);
$s_icon2->drawLine(20, 0);
$s_icon2->drawCurve(83,  -105, 157, -8);
$s_icon2->drawCurve(86,  -4,   194, 37);
$s_icon2->drawCurve(-31, -85,  -86, -36);
$s_icon2->drawCurve(-78, -34,  -96, 16);
$s_icon2->drawCurve(-96, 16,   -68, 56);
$s_icon2->drawCurve(-74, 61,   -11, 86);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(1782, -1661);
$s_icon2->setLeftFill(0xb8, 0xb8, 0xd9);
$s_icon2->drawLine(-100, 220);
$s_icon2->drawLine(20,   0);
$s_icon2->drawLine(80,   -100);
$s_icon2->drawCurve(45,   260,  236,  116);
$s_icon2->drawCurve(218,  108,  261,  -64);
$s_icon2->drawCurve(-29,  -71,  -184, -32);
$s_icon2->drawCurve(-229, -40,  -38,  -21);
$s_icon2->drawCurve(-95,  -50,  -69,  -119);
$s_icon2->drawCurve(-69,  -140, -47,  -67);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(-2598, -1361);
$s_icon2->setLeftFill(0xb8, 0xb8, 0xd9);
$s_icon2->drawLine(100,  0);
$s_icon2->drawLine(-100, -220);
$s_icon2->drawLine(0,    220);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(1242, -1541);
$s_icon2->setLeftFill(0xff, 0xff, 0xff);
$s_icon2->drawLine(-60, 40);
$s_icon2->drawLine(120, 240);
$s_icon2->drawLine(20,  0);
$s_icon2->drawLine(-80, -280);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(1982, -861);
$s_icon2->setLeftFill(0xb8, 0xb8, 0xd9);
$s_icon2->drawCurve(181,  126,  206,  18);
$s_icon2->drawCurve(235,  20,   154,  -144);
$s_icon2->drawCurve(131,  -130, -7,   -90);
$s_icon2->drawCurve(-188, 202,  -230, 24);
$s_icon2->drawCurve(-132, 14,   -350, -60);
$s_icon2->drawLine(0, 20);
$s_icon2->movePenTo(722, -1241);
$s_icon2->drawLine(20,   180);
$s_icon2->drawLine(40,   0);
$s_icon2->drawLine(40,   -120);
$s_icon2->drawLine(-100, -60);
$s_icon2->movePenTo(-595, -849);
$s_icon2->drawCurve(41,  83,  106, 120);
$s_icon2->drawCurve(131, 148, 36,  52);
$s_icon2->drawCurve(84,  122, 19,  123);
$s_icon2->drawLine(20, 0);
$s_icon2->drawCurve(48,   -216, -203, -264);
$s_icon2->drawCurve(-252, -280, -73,  -140);
$s_icon2->drawCurve(-17,  132,  60,   120);
$s_icon2->movePenTo(-2778, -901);
$s_icon2->drawLine(20, 0);
$s_icon2->drawCurve(73,  -138, 162, -29);
$s_icon2->drawCurve(99,  -17,  206, 24);
$s_icon2->drawCurve(-55, -80,  -91, -23);
$s_icon2->drawCurve(-84, -21,  -92, 30);
$s_icon2->drawCurve(-89, 30,   -65, 66);
$s_icon2->drawCurve(-68, 71,   -16, 87);
$s_icon2->movePenTo(-2858, -721);
$s_icon2->drawLine(-200, -280);
$s_icon2->drawCurve(-47, 325, 247, -25);
$s_icon2->drawLine(0, -20);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(1102, -795);
$s_icon2->setLeftFill(0x00, 0x00, 0x00);
$s_icon2->drawCurve(-299, 24,  -163, 104);
$s_icon2->drawCurve(-189, 120, -83,  266);
$s_icon2->drawCurve(-82,  264, 53,   286);
$s_icon2->drawCurve(51,   274, 161,  235);
$s_icon2->drawCurve(163,  237, 236,  137);
$s_icon2->drawCurve(251,  146, 281,  0);
$s_icon2->drawLine(340, -16);
$s_icon2->drawCurve(185,  -36,  108,  -128);
$s_icon2->drawCurve(216,  -256, -19,  -343);
$s_icon2->drawCurve(-18,  -315, -203, -281);
$s_icon2->drawCurve(-203, -282, -294, -119);
$s_icon2->drawCurve(-320, -129, -312, 119);
$s_icon2->drawCurve(-213, 81,   -41,  294);
$s_icon2->drawCurve(-37,  261,  107,  210);
$s_icon2->drawCurve(236,  471,  668,  49);
$s_icon2->drawLine(-60, -260);
$s_icon2->drawCurve(-204, -19,  -180, -124);
$s_icon2->drawCurve(-195, -133, -55,  -184);
$s_icon2->drawCurve(-69,  -228, 153,  -170);
$s_icon2->drawCurve(156,  -172, 234,  64);
$s_icon2->drawCurve(164,  47,   156,  173);
$s_icon2->drawCurve(152,  168,  76,   212);
$s_icon2->drawCurve(81,   226,  -43,  182);
$s_icon2->drawCurve(-48,  206,  -202, 98);
$s_icon2->drawCurve(-171, 82,   -325, -75);
$s_icon2->drawCurve(-207, -47,  -189, -174);
$s_icon2->drawCurve(-184, -168, -102, -227);
$s_icon2->drawCurve(-108, -238, 23,   -218);
$s_icon2->drawCurve(26,   -242, 184,  -162);
$s_icon2->drawCurve(156,  -140, 223,  -12);
$s_icon2->drawCurve(98,   -5,   320,  41);
$s_icon2->drawCurve(-32,  -107, -152, -45);
$s_icon2->drawCurve(-119, -34,  -137, 12);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(997, -640);
$s_icon2->setLeftFill(0xb8, 0xb8, 0xd9);
$s_icon2->drawCurve(-267, 49,   -108, 250);
$s_icon2->drawCurve(225,  -156, 192,  -34);
$s_icon2->drawCurve(159,  -28,  304,  38);
$s_icon2->drawLine(0, -100);
$s_icon2->drawCurve(-345, -48, -160, 29);
$s_icon2->movePenTo(-838, -601);
$s_icon2->drawLine(-20, -180);
$s_icon2->drawLine(0,   180);
$s_icon2->drawLine(20,  0);
$s_icon2->movePenTo(-698, -661);
$s_icon2->drawLine(140, 480);
$s_icon2->drawLine(20,  0);
$s_icon2->drawCurve(41, -332, -201, -148);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(1422, -641);
$s_icon2->setLeftFill(0xff, 0xff, 0xff);
$s_icon2->drawLine(0,   20);
$s_icon2->drawLine(60,  0);
$s_icon2->drawLine(-60, -20);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(-2978, -361);
$s_icon2->setLeftFill(0xb8, 0xb8, 0xd9);
$s_icon2->drawLine(20, 0);
$s_icon2->drawCurve(148,  -149, 272,  29);
$s_icon2->drawCurve(-113, -105, -147, 35);
$s_icon2->drawCurve(-151, 36,   -29,  154);
$s_icon2->movePenTo(-878, -581);
$s_icon2->drawLine(0,   60);
$s_icon2->drawLine(20,  0);
$s_icon2->drawLine(-20, -60);
$s_icon2->movePenTo(-1498, -561);
$s_icon2->drawLine(-80, 799);
$s_icon2->drawCurve(587,  0,   113,  -379);
$s_icon2->drawCurve(-189, 151, -116, 54);
$s_icon2->drawCurve(-157, 74,  -198, 0);
$s_icon2->drawLine(40, -699);
$s_icon2->setLeftFill();
$s_icon2->setRightFill();
$s_icon2->movePenTo(3262, 1358);
$s_icon2->setLeftFill(0xb8, 0xb8, 0xd9);
$s_icon2->drawCurve(-86,  165,  -193, 189);
$s_icon2->drawCurve(-112, 109,  -229, 187);
$s_icon2->drawCurve(-629, 502,  -708, 55);
$s_icon2->drawCurve(-743, 57,   -640, -458);
$s_icon2->drawCurve(-197, -141, -249, -270);
$s_icon2->drawCurve(-333, -360, -81,  -75);
$s_icon2->drawCurve(-13,  109,  53,   89);
$s_icon2->drawCurve(21,   35,   112,  127);
$s_icon2->drawCurve(276,  313,  351,  260);
$s_icon2->drawCurve(645,  481,  791,  -73);
$s_icon2->drawCurve(742,  -68,  638,  -523);
$s_icon2->drawCurve(253,  -204, 108,  -111);
$s_icon2->drawCurve(210,  -217, 33,   -178);
$s_icon2->drawLine(-20, 0);
$s_icon2->movePenTo(1002, -141);
$s_icon2->drawCurve(217,  -130, 198,  37);
$s_icon2->drawCurve(176,  34,   142,  160);
$s_icon2->drawCurve(128,  145,  73,   217);
$s_icon2->drawCurve(70,   210,  -4,   206);
$s_icon2->drawCurve(122,  -229, -122, -321);
$s_icon2->drawCurve(-115, -301, -225, -148);
$s_icon2->drawCurve(-182, -119, -191, 20);
$s_icon2->drawCurve(-212, 22,   -75,  197);
$s_icon2->movePenTo(715, 67);
$s_icon2->drawCurve(-9,  -110, -24,  -58);
$s_icon2->drawCurve(-37, 463,  266,  293);
$s_icon2->drawCurve(261, 287,  470,  36);
$s_icon2->drawCurve(-21, -95,  -230, -77);
$s_icon2->drawLine(-185, -61);
$s_icon2->drawCurve(-99,  -37,  -45, -37);
$s_icon2->drawCurve(-261, -203, -63, -230);
$s_icon2->drawCurve(-13,  -46,  -10, -125);
$s_icon2->movePenTo(302, -101);
$s_icon2->drawCurve(-83,  436,  216,  406);
$s_icon2->drawCurve(216,  409,  411,  174);
$s_icon2->drawCurve(281,  122,  321,  -39);
$s_icon2->drawCurve(375,  -45,  103,  -264);
$s_icon2->drawCurve(-223, 155,  -254, 35);
$s_icon2->drawCurve(-241, 33,   -238, -79);
$s_icon2->drawCurve(-233, -78,  -192, -171);
$s_icon2->drawCurve(-196, -173, -120, -242);
$s_icon2->drawCurve(-59,  -117, -21,  -227);
$s_icon2->drawCurve(-24,  -239, -39,  -96);
$s_icon2->movePenTo(-2258, -441);
$s_icon2->drawLine(0,   140);
$s_icon2->drawLine(20,  0);
$s_icon2->drawLine(-20, -140);
$s_icon2->movePenTo(-2278, -201);
$s_icon2->drawCurve(0,   568,  246, 224);
$s_icon2->drawCurve(166, 146,  238, 49);
$s_icon2->drawCurve(227, 47,   234, -52);
$s_icon2->drawCurve(237, -54,  174, -140);
$s_icon2->drawCurve(188, -151, 70,  -218);
$s_icon2->drawLine(-20, 0);
$s_icon2->drawCurve(-128, 172,  -184, 124);
$s_icon2->drawCurve(-189, 127,  -204, 47);
$s_icon2->drawCurve(-472, 109,  -320, -339);
$s_icon2->drawCurve(-109, -115, -53,  -204);
$s_icon2->drawLine(-41, -169);
$s_icon2->drawCurve(-27, -100, -33, -71);
$s_icon2->movePenTo(-3318, -281);
$s_icon2->drawCurve(-27, 225, 136, 145);
$s_icon2->drawCurve(147, 158, 204, -129);
$s_icon2->drawLine(0, -20);
$s_icon2->drawCurve(-188, -5,  -105, -110);
$s_icon2->drawCurve(-31,  -34, -136, -230);
$s_icon2->movePenTo(-2722, 231);
$s_icon2->drawCurve(-60, 81, -16, 106);
$s_icon2->drawLine(20,  0);
$s_icon2->drawLine(220, -240);
$s_icon2->drawCurve(-89, -49, -75, 102);
$s_icon2->movePenTo(-3138, 518);
$s_icon2->drawCurve(0,    134, 17,   70);
$s_icon2->drawCurve(26,   104, 77,   65);
$s_icon2->drawCurve(96,   85,  141,  10);
$s_icon2->drawCurve(159,  11,  44,   -119);
$s_icon2->drawCurve(-222, 0,   -117, -85);
$s_icon2->drawCurve(-87,  -64, -134, -211);
$s_icon2->movePenTo(-2398, 998);
$s_icon2->drawLine(20,  0);
$s_icon2->drawLine(180, -160);
$s_icon2->drawCurve(-80, -32, -56, 57);
$s_icon2->drawCurve(-46, 47,  -18, 88);
$s_icon2->movePenTo(-598, -21);
$s_icon2->drawLine(20,  0);
$s_icon2->drawLine(-20, -60);
$s_icon2->drawLine(0,   60);
$s_icon2->movePenTo(-578, -161);
$s_icon2->drawLine(0,   60);
$s_icon2->drawLine(20,  0);
$s_icon2->drawLine(-20, -60);
$s_icon2->movePenTo(-168, 562);
$s_icon2->drawCurve(94, -131, -24, -93);
$s_icon2->drawLine(-260, 380);
$s_icon2->drawLine(20,   20);
$s_icon2->drawCurve(101, -81, 69, -95);
$s_icon2->movePenTo(-698, 818);
$s_icon2->drawLine(-60, 60);
$s_icon2->drawLine(0,   20);
$s_icon2->drawCurve(307, 308,  131, 122);
$s_icon2->drawCurve(265, 244,  228, 154);
$s_icon2->drawCurve(599, 403,  570, -75);
$s_icon2->drawCurve(309, -39,  351, -191);
$s_icon2->drawCurve(294, -154, 122, -85);
$s_icon2->drawCurve(275, -191, 9,   -156);
$s_icon2->drawLine(-390, 293);
$s_icon2->drawCurve(-232, 175,  -178, 85);
$s_icon2->drawCurve(-430, 205,  -467, -64);
$s_icon2->drawCurve(-434, -60,  -409, -284);
$s_icon2->drawCurve(-184, -127, -256, -250);
$s_icon2->drawCurve(-315, -306, -105, -87);
$s_icon2->movePenTo(-1658, 1431);
$s_icon2->drawCurve(-313, 133,  -261, -74);
$s_icon2->drawCurve(-262, -75,  -184, -277);
$s_icon2->drawCurve(0,    267,  181,  123);
$s_icon2->drawCurve(158,  107,  265,  -18);
$s_icon2->drawCurve(240,  -17,  240,  -109);
$s_icon2->drawCurve(239,  -109, 117,  -144);
$s_icon2->drawCurve(-86,  -8,   -129, 78);
$s_icon2->drawLine(-205, 123);
$s_icon2->movePenTo(-898, 1298);
$s_icon2->drawLine(-20, -60);
$s_icon2->drawLine(0,   60);
$s_icon2->drawLine(20,  0);

# icon3 Movieclip
my $i_icon2 = new SWF::Sprite();
my $item    = $i_icon2->add($s_icon2);
$item->scaleTo(0.5);
$i_icon2->nextFrame();

# icon1 Shapes
my $s_icon1 = new SWF::Shape();
$s_icon1->movePenTo(6960, 2721);
$s_icon1->setLeftFill(0xab, 0x97, 0xfd);
$s_icon1->setRightFill(0x01, 0x01, 0x01);
$s_icon1->drawCurve(-210, 0,    -370, -108);
$s_icon1->drawCurve(-397, -117, -183, -12);
$s_icon1->drawCurve(-191, -13,  -375, 85);
$s_icon1->drawCurve(-367, 83,   -187, -18);
$s_icon1->drawCurve(-80,  -8,   -114, -61);
$s_icon1->drawCurve(-142, -76,  -44,  -15);
$s_icon1->drawCurve(-17,  176,  7,    76);
$s_icon1->drawCurve(13,   135,  80,   93);
$s_icon1->drawCurve(70,   80,   122,  54);
$s_icon1->drawCurve(120,  52,   130,  9);
$s_icon1->drawCurve(309,  19,   106,  -214);
$s_icon1->drawCurve(-302, 0,    -158, -220);
$s_icon1->drawCurve(64,   -12,  125,  24);
$s_icon1->drawCurve(127,  24,   64,   -10);
$s_icon1->drawCurve(45,   -7,   102,  -38);
$s_icon1->drawCurve(93,   -34,  60,   -4);
$s_icon1->drawCurve(228,  -17,  308,  73);
$s_icon1->drawCurve(177,  42,   347,  102);
$s_icon1->drawLine(343, 97);
$s_icon1->drawCurve(193,  67,   117,  99);
$s_icon1->drawCurve(91,   75,   54,   126);
$s_icon1->drawCurve(24,   54,   51,   174);
$s_icon1->drawCurve(41,   141,  45,   72);
$s_icon1->drawCurve(66,   103,  115,  49);
$s_icon1->drawCurve(356,  155,  280,  -58);
$s_icon1->drawCurve(334,  -69,  90,   -368);
$s_icon1->drawCurve(31,   -119, -31,  -141);
$s_icon1->drawCurve(-226, 104,  -217, -10);
$s_icon1->drawCurve(-189, -10,  -248, -104);
$s_icon1->drawLine(0, -40);
$s_icon1->drawCurve(68, -13, 102, 16);
$s_icon1->drawLine(179, 32);
$s_icon1->drawCurve(223,  38,   121,  -90);
$s_icon1->drawCurve(262,  -193, -276, -280);
$s_icon1->drawCurve(-161, -164, -318, -170);
$s_icon1->drawCurve(-330, -173, -454, -126);
$s_icon1->drawLine(-816, -194);
$s_icon1->drawCurve(-357, -91, -179, -40);
$s_icon1->drawCurve(-311, -70, -233, -2);
$s_icon1->drawCurve(-268, 0,   -168, 30);
$s_icon1->drawCurve(-229, 42,  -175, 111);
$s_icon1->drawCurve(-112, 68,  -41,  105);
$s_icon1->drawCurve(-46,  117, 75,   103);
$s_icon1->drawCurve(142,  191, 288,  -44);
$s_icon1->drawCurve(81,   -12, 163,  -46);
$s_icon1->drawCurve(159,  -45, 71,   -10);
$s_icon1->drawCurve(256,  -37, 349,  65);
$s_icon1->drawLine(595, 142);
$s_icon1->drawCurve(179, 44, 67, 25);
$s_icon1->drawCurve(146, 53, 68, 78);
$s_icon1->movePenTo(8890, 3055);
$s_icon1->setLeftFill(0x01, 0x01, 0x01);
$s_icon1->setRightFill();
$s_icon1->drawLine(-184, -161);
$s_icon1->drawCurve(-44,  -46,  -92,  -148);
$s_icon1->drawCurve(-79,  -126, -75,  -60);
$s_icon1->drawCurve(-365, -300, -771, -257);
$s_icon1->drawCurve(-573, -192, -402, -78);
$s_icon1->drawCurve(-544, -105, -461, 45);
$s_icon1->drawCurve(-503, 48,   -237, 96);
$s_icon1->drawCurve(-60,  23,   -111, 68);
$s_icon1->drawCurve(-111, 67,   -58,  22);
$s_icon1->drawCurve(-40,  15,   -82,  3);
$s_icon1->drawCurve(-82,  3,    -40,  15);
$s_icon1->drawCurve(-53,  20,   -119, 105);
$s_icon1->drawCurve(-107, 94,   -71,  11);
$s_icon1->drawCurve(-56,  9,    -118, -51);
$s_icon1->drawCurve(-129, -61,  -43,  -17);
$s_icon1->drawCurve(-527, -190, -273, 164);
$s_icon1->drawCurve(-188, 109,  28,   283);
$s_icon1->drawCurve(13,   121,  53,   108);
$s_icon1->drawCurve(54,   110,  80,   59);
$s_icon1->drawCurve(258,  187,  355,  -123);
$s_icon1->drawCurve(333,  -116, 174,  -288);
$s_icon1->drawCurve(-23,  128,  -126, 169);
$s_icon1->drawCurve(-70,  95,   -141, 168);
$s_icon1->drawCurve(-405, 538,  -135, 304);
$s_icon1->drawCurve(-244, 549,  214,  409);
$s_icon1->drawCurve(103,  203,  218,  138);
$s_icon1->drawCurve(114,  73,   315,  136);
$s_icon1->drawLine(980, 430);
$s_icon1->drawCurve(1131, 483,  649, 193);
$s_icon1->drawCurve(888,  261,  572, -261);
$s_icon1->drawCurve(214,  -102, 246, -247);
$s_icon1->drawLine(400, -427);
$s_icon1->drawLine(377, -387);
$s_icon1->drawCurve(216, -246, 67, -207);
$s_icon1->drawCurve(46,  -139, 10, -207);
$s_icon1->drawLine(7, -354);
$s_icon1->drawCurve(16, -277, -3,  -138);
$s_icon1->drawCurve(-6, -239, -77, -166);
$s_icon1->drawLine(-27, -51);
$s_icon1->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1->drawLine(14, 85);
$s_icon1->drawCurve(23,    232,  -120, 314);
$s_icon1->drawCurve(-250,  661,  -643, 925);
$s_icon1->drawCurve(-183,  258,  -82,  85);
$s_icon1->drawCurve(-159,  165,  -206, 66);
$s_icon1->drawCurve(-309,  97,   -361, -54);
$s_icon1->drawCurve(-207,  -31,  -443, -136);
$s_icon1->drawCurve(-1336, -403, -844, -343);
$s_icon1->drawCurve(-396,  -159, -159, -91);
$s_icon1->drawCurve(-296,  -170, -149, -234);
$s_icon1->drawCurve(-275,  -428, 295,  -570);
$s_icon1->drawCurve(100,   -194, 198,  -266);
$s_icon1->drawLine(302, -405);
$s_icon1->drawCurve(37,  -55,  111, -125);
$s_icon1->drawCurve(102, -115, 40,  -68);
$s_icon1->drawCurve(14,  -23,  11,  -42);
$s_icon1->drawLine(18, -69);
$s_icon1->drawCurve(27, -78, 76,  -8);
$s_icon1->drawCurve(64, -7,  23,  86);
$s_icon1->drawCurve(18, 68,  -15, 63);
$s_icon1->drawLine(-57, 248);
$s_icon1->drawCurve(-23, 149,  44,  99);
$s_icon1->drawCurve(45,  104,  161, 111);
$s_icon1->drawCurve(77,  54,   187, 101);
$s_icon1->drawCurve(362, 200,  568, 165);
$s_icon1->drawCurve(323, 95,   647, 170);
$s_icon1->drawCurve(227, 67,   465, 162);
$s_icon1->drawCurve(420, 137,  288, 34);
$s_icon1->drawCurve(199, 24,   202, -62);
$s_icon1->drawCurve(198, -60,  156, -128);
$s_icon1->drawCurve(161, -133, 79,  -177);
$s_icon1->drawCurve(84,  -190, -26, -210);
$s_icon1->drawCurve(-8,  -72,  -55, -83);
$s_icon1->drawLine(-90, -145);
$s_icon1->drawLine(50,  14);
$s_icon1->setLeftFill();
$s_icon1->drawCurve(221, 72, 55, 223);
$s_icon1->movePenTo(8526, 5034);
$s_icon1->setLeftFill(0xff, 0xff, 0xff);
$s_icon1->drawCurve(-3, 3,   7,  14);
$s_icon1->drawCurve(3,  -24, -7, 7);
$s_icon1->movePenTo(7747, 4921);
$s_icon1->setLeftFill(0x01, 0x01, 0x01);
$s_icon1->drawCurve(101, 43,  112,  17);
$s_icon1->drawCurve(30,  -58, 61,   -105);
$s_icon1->drawCurve(51,  -93, 18,   -84);
$s_icon1->drawCurve(-86, -31, -274, -69);
$s_icon1->drawLine(-95, 113);
$s_icon1->drawCurve(-55, 71, -4,  56);
$s_icon1->drawCurve(-2,  78, 143, 62);
$s_icon1->movePenTo(7403, 4394);
$s_icon1->drawCurve(-98, -20, -82, 50);
$s_icon1->drawCurve(-98, 58,  -2,  82);
$s_icon1->drawCurve(-2,  71,  68,  67);
$s_icon1->drawCurve(64,  63,  85,  24);
$s_icon1->drawCurve(92,  26,  60,  -38);
$s_icon1->drawCurve(104, -64, 12,  -88);
$s_icon1->drawCurve(10,  -77, -62, -69);
$s_icon1->drawCurve(-60, -66, -91, -19);
$s_icon1->movePenTo(7011, 4245);
$s_icon1->drawCurve(-112, -53, -59,  12);
$s_icon1->drawCurve(-118, 23,  -15,  111);
$s_icon1->drawCurve(-14,  99,  66,   102);
$s_icon1->drawCurve(68,   104, 90,   8);
$s_icon1->drawCurve(106,  10,  80,   -140);
$s_icon1->drawCurve(22,   -41, 55,   -159);
$s_icon1->drawCurve(-17,  -5,  -152, -71);
$s_icon1->movePenTo(6604, 4692);
$s_icon1->setLeftFill(0xff, 0x01, 0x01);
$s_icon1->setRightFill(0x01, 0x01, 0x01);
$s_icon1->drawCurve(-115, 19,   -29, 150);
$s_icon1->drawCurve(117,  56,   163, 44);
$s_icon1->drawCurve(75,   -146, 25,  -74);
$s_icon1->drawCurve(-150, -63,  -86, 14);
$s_icon1->movePenTo(6543, 4644);
$s_icon1->setLeftFill(0x01, 0x01, 0x01);
$s_icon1->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1->drawCurve(-89,  47,   -29,  96);
$s_icon1->drawCurve(-32,  103,  87,   54);
$s_icon1->drawCurve(56,   31,   64,   19);
$s_icon1->drawCurve(76,   27,   104,  0);
$s_icon1->drawCurve(83,   -157, 3,    -80);
$s_icon1->drawCurve(4,    -65,  -114, -42);
$s_icon1->drawCurve(-142, -71,  -71,  38);
$s_icon1->movePenTo(6969, 4928);
$s_icon1->drawCurve(-70, 113, 21,  40);
$s_icon1->drawCurve(27,  56,  110, 23);
$s_icon1->drawCurve(93,  18,  -10, 43);
$s_icon1->drawLine(90, -13);
$s_icon1->drawCurve(31, -11, 39, -56);
$s_icon1->drawLine(68, -93);
$s_icon1->drawCurve(42,   -62, -10,  -45);
$s_icon1->drawCurve(-13,  -48, -129, -57);
$s_icon1->drawCurve(-129, -57, -49,  22);
$s_icon1->drawCurve(-45,  19,  -66,  108);
$s_icon1->movePenTo(7029, 5315);
$s_icon1->drawCurve(-83,  -34, -46,  -7);
$s_icon1->drawCurve(-70,  -13, -104, 114);
$s_icon1->drawCurve(-103, 114, 20,   68);
$s_icon1->drawCurve(24,   74,  140,  45);
$s_icon1->drawCurve(139,  46,  64,   -41);
$s_icon1->drawCurve(66,   -39, 42,   -103);
$s_icon1->drawCurve(36,   -86, 6,    -92);
$s_icon1->drawCurve(-41,  -9,  -90,  -37);
$s_icon1->movePenTo(7193, 5081);
$s_icon1->setLeftFill(0xff, 0xff, 0xff);
$s_icon1->setRightFill(0x01, 0x01, 0x01);
$s_icon1->drawCurve(65, -18, 82, -142);
$s_icon1->drawLine(-260, -80);
$s_icon1->drawLine(-100, 200);
$s_icon1->drawCurve(160, 55, 53, -15);
$s_icon1->movePenTo(6552, 5178);
$s_icon1->setLeftFill(0x01, 0x01, 0x01);
$s_icon1->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1->drawCurve(-74,  -72,  -198, -45);
$s_icon1->drawCurve(-144, 202,  61,   112);
$s_icon1->drawCurve(55,   100,  248,  66);
$s_icon1->drawCurve(140,  -215, -58,  -109);
$s_icon1->drawLine(-23, -32);
$s_icon1->setRightFill(0xff, 0xff, 0xff);
$s_icon1->drawCurve(-12,  47,   -67,  149);
$s_icon1->drawCurve(-160, -16,  -120, -64);
$s_icon1->drawCurve(41,   -157, 90,   -17);
$s_icon1->drawCurve(63,   -12,  158,  63);
$s_icon1->setLeftFill(0xc9, 0xc9, 0xc9);
$s_icon1->drawLine(8,  3);
$s_icon1->drawLine(-1, 4);
$s_icon1->movePenTo(7232, 5819);
$s_icon1->setLeftFill(0x01, 0x01, 0x01);
$s_icon1->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1->drawCurve(77,  40,  124, -28);
$s_icon1->drawCurve(125, -28, 52,  -72);
$s_icon1->drawCurve(61,  -82, -88, -95);
$s_icon1->drawCurve(-26, -28, -81, -32);
$s_icon1->drawCurve(-93, -38, -23, -15);
$s_icon1->drawCurve(-93, 85,  -27, 38);
$s_icon1->drawCurve(-37, 55,  -17, 42);
$s_icon1->drawCurve(-43, 112, 89,  46);
$s_icon1->movePenTo(7007, 5486);
$s_icon1->setLeftFill(0xff, 0xff, 0xff);
$s_icon1->setRightFill(0x01, 0x01, 0x01);
$s_icon1->drawCurve(38, -69, 15, -56);
$s_icon1->drawLine(-200, -60);
$s_icon1->drawCurve(-125, 199, -15, 41);
$s_icon1->drawLine(220, 60);
$s_icon1->drawLine(67,  -115);
$s_icon1->movePenTo(7826, 5260);
$s_icon1->setLeftFill(0x01, 0x01, 0x01);
$s_icon1->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1->drawCurve(50,   -84, 4,    -115);
$s_icon1->drawCurve(-223, -60, -72,  26);
$s_icon1->drawCurve(-73,  26,  -132, 188);
$s_icon1->drawCurve(72,   96,  89,   33);
$s_icon1->drawCurve(83,   30,  77,   -31);
$s_icon1->drawCurve(77,   -30, 48,   -79);
$s_icon1->movePenTo(7796, 5107);
$s_icon1->setLeftFill(0xff, 0xff, 0xff);
$s_icon1->setRightFill(0x01, 0x01, 0x01);
$s_icon1->drawCurve(-63,  -129, -129, 70);
$s_icon1->drawCurve(-117, 63,   -27,  110);
$s_icon1->drawLine(140, 53);
$s_icon1->drawCurve(74, 20,  77,  -52);
$s_icon1->drawCurve(82, -56, -37, -79);
$s_icon1->movePenTo(7350, 6527);
$s_icon1->drawLine(2, -9);
$s_icon1->drawCurve(-1, -8, -5, 7);
$s_icon1->drawCurve(-3, 20, 7,  -10);
$s_icon1->movePenTo(3956, 3921);
$s_icon1->drawCurve(-277, 390, -59, 210);
$s_icon1->drawCurve(175,  97,  290, 110);
$s_icon1->drawLine(475, 186);
$s_icon1->drawCurve(345, 163, 131, -46);
$s_icon1->drawCurve(56,  -21, 58,  -77);
$s_icon1->drawLine(90, -126);
$s_icon1->drawCurve(324, -399, -61,  -141);
$s_icon1->drawCurve(-33, -74,  -242, -61);
$s_icon1->drawLine(-328, -75);
$s_icon1->drawCurve(-306, -105, -234, -108);
$s_icon1->drawLine(-117, -59);
$s_icon1->drawCurve(-75, -35, -45, 4);
$s_icon1->drawCurve(-49, 4,   -49, 62);
$s_icon1->drawCurve(-28, 34,  -41, 67);
$s_icon1->movePenTo(3904, 3788);
$s_icon1->setLeftFill(0x01, 0x01, 0x01);
$s_icon1->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1->drawLine(-114, 173);
$s_icon1->drawLine(-195, 276);
$s_icon1->drawCurve(-125, 202, 23,  102);
$s_icon1->drawCurve(17,   70,  131, 75);
$s_icon1->drawLine(199, 95);
$s_icon1->drawCurve(519, 256, 421, 97);
$s_icon1->drawCurve(254, 61,  99,  -54);
$s_icon1->drawCurve(66,  -38, 79,  -117);
$s_icon1->drawLine(112, -185);
$s_icon1->drawCurve(111,  -145, 56,   -85);
$s_icon1->drawCurve(101,  -154, -8,   -106);
$s_icon1->drawCurve(-6,   -78,  -78,  -50);
$s_icon1->drawCurve(-31,  -20,  -115, -46);
$s_icon1->drawCurve(-240, -90,  -480, -166);
$s_icon1->drawCurve(-208, -66,  -252, -101);
$s_icon1->drawCurve(-63,  -31,  -34,  -13);
$s_icon1->drawCurve(-61,  -23,  -39,  17);
$s_icon1->drawCurve(-61,  29,   -78,  115);
$s_icon1->movePenTo(5780, 4921);
$s_icon1->drawCurve(65, -21, 75, 18);
$s_icon1->drawCurve(44, 11,  96, 32);
$s_icon1->drawLine(-100, -100);
$s_icon1->drawLine(-104, 3);
$s_icon1->drawCurve(-48, 9, -28, 48);
$s_icon1->movePenTo(5952, 4584);
$s_icon1->drawCurve(-9,  69,  44,  70);
$s_icon1->drawCurve(44,  68,  69,  31);
$s_icon1->drawCurve(74,  33,  66,  -31);
$s_icon1->drawCurve(118, -56, 23,  -85);
$s_icon1->drawCurve(21,  -73, -53, -71);
$s_icon1->drawCurve(-51, -68, -85, -24);
$s_icon1->drawCurve(-92, -26, -78, 43);
$s_icon1->drawCurve(-80, 43,  -11, 77);
$s_icon1->movePenTo(5936, 5301);
$s_icon1->drawCurve(86,  -14,  50,  -116);
$s_icon1->drawCurve(45,  -105, -17, -85);
$s_icon1->drawCurve(-98, 97,   -62, 123);
$s_icon1->drawLine(-240, -100);
$s_icon1->drawLine(39,   -87);
$s_icon1->drawCurve(21, -52, 0, -41);
$s_icon1->drawLine(-67, 83);
$s_icon1->drawCurve(-38, 53, -2,  40);
$s_icon1->drawCurve(-5,  70, 113, 73);
$s_icon1->drawCurve(106, 69, 69,  -8);
$s_icon1->movePenTo(6104, 6526);
$s_icon1->setLeftFill(0xff, 0xff, 0xff);
$s_icon1->setRightFill();
$s_icon1->drawLine(9, 1);
$s_icon1->drawCurve(0, -20, -10, 10);
$s_icon1->drawLine(1, 9);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(3686, 2737);
$s_icon1->setLeftFill(0xec, 0xec, 0xec);
$s_icon1->drawCurve(-3, 20, 7, -10);
$s_icon1->drawLine(2, -9);
$s_icon1->drawCurve(-1, -8, -5, 7);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(5820, 3007);
$s_icon1->setLeftFill(0xff, 0xff, 0xff);
$s_icon1->drawCurve(354, 77,  366, 147);
$s_icon1->drawCurve(97,  41,  103, 107);
$s_icon1->drawCurve(125, 131, 55,  37);
$s_icon1->drawCurve(70,  49,  132, 42);
$s_icon1->drawLine(218, 63);
$s_icon1->drawLine(-20, -140);
$s_icon1->drawCurve(-180, -6, -119, -122);
$s_icon1->drawLine(-100, -113);
$s_icon1->drawCurve(-64,  -70,  -64,  -43);
$s_icon1->drawCurve(-237, -156, -383, -72);
$s_icon1->drawCurve(-397, -74,  -256, 76);
$s_icon1->drawCurve(48,   20,   97,   -1);
$s_icon1->drawLine(155, 7);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(8720, 3541);
$s_icon1->setLeftFill(0xee, 0xeb, 0xff);
$s_icon1->drawLine(0, 80);
$s_icon1->drawCurve(15, -39, -15, -41);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(8700, 3621);
$s_icon1->setLeftFill(0xee, 0xeb, 0xff);
$s_icon1->drawLine(0, 60);
$s_icon1->drawCurve(12, -29, -12, -31);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(8686, 3697);
$s_icon1->setLeftFill(0xff, 0xff, 0xff);
$s_icon1->drawCurve(-3, 20, 7, -10);
$s_icon1->drawLine(2, -9);
$s_icon1->drawCurve(-1, -8, -5, 7);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(9280, 3701);
$s_icon1->setLeftFill(0xff, 0xff, 0xff);
$s_icon1->drawCurve(-21,  153, -121, 227);
$s_icon1->drawCurve(-145, 250, -57,  110);
$s_icon1->drawCurve(-382, 775, -301, 465);
$s_icon1->drawCurve(-200, 310, -149, 131);
$s_icon1->drawCurve(-214, 188, -290, 27);
$s_icon1->drawCurve(-207, 22,  -262, -62);
$s_icon1->drawCurve(-61,  -15, -390, -118);
$s_icon1->drawLine(-1420, -477);
$s_icon1->drawLine(-823,  -263);
$s_icon1->drawCurve(-464, -160, -333, -167);
$s_icon1->drawCurve(-552, -271, -68,  -425);
$s_icon1->drawCurve(-58,  139,  79,   165);
$s_icon1->drawCurve(68,   143,  131,  96);
$s_icon1->drawCurve(229,  171,  372,  166);
$s_icon1->drawCurve(213,  96,   426,  174);
$s_icon1->drawCurve(1304, 565,  856,  225);
$s_icon1->drawCurve(402,  106,  154,  20);
$s_icon1->drawCurve(318,  41,   246,  -94);
$s_icon1->drawCurve(344,  -134, 314,  -351);
$s_icon1->drawCurve(175,  -197, 317,  -471);
$s_icon1->drawLine(186, -263);
$s_icon1->drawCurve(112, -158, 66,  -108);
$s_icon1->drawCurve(189, -311, 40,  -280);
$s_icon1->drawCurve(47,  -317, -70, -123);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(7013, 4251);
$s_icon1->setLeftFill(0xec, 0xec, 0xec);
$s_icon1->drawLine(10, 3);
$s_icon1->drawCurve(10, -7, -20, 4);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(6693, 4691);
$s_icon1->setLeftFill(0xb8, 0x01, 0x01);
$s_icon1->drawLine(10, 3);
$s_icon1->drawCurve(10, -7, -20, 4);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(7093, 4791);
$s_icon1->setLeftFill(0xec, 0xec, 0xec);
$s_icon1->drawLine(10, 3);
$s_icon1->drawCurve(10, -7, -20, 4);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(3890, 4811);
$s_icon1->setLeftFill(0xec, 0xec, 0xec);
$s_icon1->drawLine(16, 3);
$s_icon1->drawCurve(7, -7, -23, 4);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(6033, 4931);
$s_icon1->setLeftFill(0xec, 0xec, 0xec);
$s_icon1->drawLine(10, 3);
$s_icon1->drawCurve(10, -7, -20, 4);
$s_icon1->movePenTo(6453, 4931);
$s_icon1->drawLine(10, 3);
$s_icon1->drawCurve(10, -7, -20, 4);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(5773, 5131);
$s_icon1->setLeftFill(0xec, 0xec, 0xec);
$s_icon1->drawLine(10, 3);
$s_icon1->drawCurve(10, -7, -20, 4);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(4780, 5141);
$s_icon1->setLeftFill(0xdd, 0xdd, 0xdd);
$s_icon1->drawCurve(41,  20,  59,  0);
$s_icon1->drawCurve(-40, -15, -60, -5);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(5913, 5311);
$s_icon1->setLeftFill(0xec, 0xec, 0xec);
$s_icon1->drawLine(10, 3);
$s_icon1->drawCurve(10, -7, -20, 4);
$s_icon1->setLeftFill();
$s_icon1->setRightFill();

$s_icon1->movePenTo(7260, 5721);
$s_icon1->setLeftFill(0xff, 0xff, 0xff);
$s_icon1->drawCurve(85,  31,  31,  2);
$s_icon1->drawCurve(78,  7,   26,  -59);
$s_icon1->drawCurve(23,  -51, -26, -61);
$s_icon1->drawCurve(-27, -62, -49, -2);
$s_icon1->drawCurve(-57, -1,  -54, 96);
$s_icon1->drawCurve(-17, 29,  -13, 71);

my $s_icon1_f2 = new SWF::Shape();
$s_icon1_f2->movePenTo(8877, 3643);
$s_icon1_f2->setLeftFill(0xc9, 0xc9, 0xc9);
$s_icon1_f2->setRightFill(0x01, 0x01, 0x01);
$s_icon1_f2->drawCurve(82,    -165, -25,  -185);
$s_icon1_f2->drawCurve(-8,    -64,  -54,  -71);
$s_icon1_f2->drawCurve(-47,   -63,  -518, -190);
$s_icon1_f2->drawCurve(-613,  -225, -866, -239);
$s_icon1_f2->drawCurve(-1929, -534, -108, 38);
$s_icon1_f2->drawLine(-178, 16);
$s_icon1_f2->drawCurve(-71, 5,   -160, 230);
$s_icon1_f2->drawCurve(-73, 104, -51,  158);
$s_icon1_f2->drawCurve(-51, 161, 17,   85);
$s_icon1_f2->drawLine(11, 98);
$s_icon1_f2->drawCurve(8,  53, 135, 163);
$s_icon1_f2->drawCurve(16, 27, 87,  41);
$s_icon1_f2->drawLine(327, 147);
$s_icon1_f2->drawCurve(493, 229, 265, 68);
$s_icon1_f2->drawLine(955, 232);
$s_icon1_f2->drawCurve(235, 63,   445, 138);
$s_icon1_f2->drawCurve(413, 120,  284, 30);
$s_icon1_f2->drawCurve(194, 20,   200, -54);
$s_icon1_f2->drawCurve(194, -52,  155, -112);
$s_icon1_f2->drawCurve(158, -118, 78,  -154);
$s_icon1_f2->movePenTo(9166, 3350);
$s_icon1_f2->setLeftFill(0x01, 0x01, 0x01);
$s_icon1_f2->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1_f2->drawCurve(9,     35,   5,    50);
$s_icon1_f2->drawCurve(23,    234,  -120, 312);
$s_icon1_f2->drawCurve(-251,  663,  -642, 923);
$s_icon1_f2->drawCurve(-183,  258,  -82,  85);
$s_icon1_f2->drawCurve(-159,  165,  -206, 66);
$s_icon1_f2->drawCurve(-308,  97,   -362, -54);
$s_icon1_f2->drawCurve(-214,  -33,  -436, -134);
$s_icon1_f2->drawCurve(-1342, -405, -838, -341);
$s_icon1_f2->drawCurve(-399,  -160, -156, -90);
$s_icon1_f2->drawCurve(-297,  -170, -148, -234);
$s_icon1_f2->drawCurve(-275,  -429, 295,  -569);
$s_icon1_f2->drawCurve(103,   -199, 195,  -261);
$s_icon1_f2->drawCurve(221,   -289, 81,   -116);
$s_icon1_f2->drawCurve(39,    -58,  109,  -122);
$s_icon1_f2->drawCurve(100,   -114, 42,   -69);
$s_icon1_f2->drawCurve(15,    -25,  10,   -40);
$s_icon1_f2->drawLine(18, -69);
$s_icon1_f2->drawCurve(27, -79, 76,  -7);
$s_icon1_f2->drawCurve(64, -7,  23,  86);
$s_icon1_f2->drawCurve(18, 68,  -15, 63);
$s_icon1_f2->drawLine(-57, 248);
$s_icon1_f2->drawCurve(-23, 149,  44,  99);
$s_icon1_f2->drawCurve(45,  104,  161, 111);
$s_icon1_f2->drawCurve(79,  56,   185, 99);
$s_icon1_f2->drawCurve(363, 201,  567, 164);
$s_icon1_f2->drawCurve(346, 102,  624, 163);
$s_icon1_f2->drawCurve(240, 72,   452, 157);
$s_icon1_f2->drawCurve(420, 137,  288, 34);
$s_icon1_f2->drawCurve(198, 24,   203, -62);
$s_icon1_f2->drawCurve(197, -60,  157, -128);
$s_icon1_f2->drawCurve(161, -134, 79,  -176);
$s_icon1_f2->drawCurve(84,  -189, -26, -211);
$s_icon1_f2->drawCurve(-8,  -73,  -55, -82);
$s_icon1_f2->drawLine(-90, -145);
$s_icon1_f2->drawLine(50,  14);
$s_icon1_f2->setRightFill();
$s_icon1_f2->drawCurve(-98,  -84,  -1940, -604);
$s_icon1_f2->drawCurve(-453, -141, -353,  -106);
$s_icon1_f2->drawLine(-226, -67);
$s_icon1_f2->drawCurve(-962, -282, -76, 30);
$s_icon1_f2->drawLine(-181, 18);
$s_icon1_f2->drawCurve(-166, 15,   -46,  28);
$s_icon1_f2->drawCurve(-107, 65,   -62,  24);
$s_icon1_f2->drawCurve(-41,  15,   -81,  3);
$s_icon1_f2->drawCurve(-81,  3,    -41,  15);
$s_icon1_f2->drawCurve(-53,  20,   -119, 105);
$s_icon1_f2->drawCurve(-106, 94,   -72,  11);
$s_icon1_f2->drawCurve(-55,  9,    -119, -51);
$s_icon1_f2->drawCurve(-131, -62,  -41,  -16);
$s_icon1_f2->drawCurve(-401, -145, -254, 61);
$s_icon1_f2->drawLine(-295, 512);
$s_icon1_f2->drawCurve(17,  87,   39,   80);
$s_icon1_f2->drawCurve(55,  111,  79,   58);
$s_icon1_f2->drawCurve(257, 187,  356,  -123);
$s_icon1_f2->drawCurve(333, -115, 174,  -289);
$s_icon1_f2->drawCurve(-22, 127,  -127, 170);
$s_icon1_f2->drawLine(-211, 263);
$s_icon1_f2->drawCurve(-406, 540, -134, 302);
$s_icon1_f2->drawCurve(-244, 549, 214,  409);
$s_icon1_f2->drawCurve(104,  203, 217,  138);
$s_icon1_f2->drawCurve(109,  70,  320,  139);
$s_icon1_f2->drawLine(980, 430);
$s_icon1_f2->drawCurve(1128, 482,  652, 194);
$s_icon1_f2->drawCurve(888,  261,  572, -261);
$s_icon1_f2->drawCurve(215,  -102, 245, -247);
$s_icon1_f2->drawCurve(139,  -140, 261, -287);
$s_icon1_f2->drawLine(377, -387);
$s_icon1_f2->drawCurve(215, -245, 68, -208);
$s_icon1_f2->drawCurve(45,  -136, 11, -210);
$s_icon1_f2->drawLine(7, -354);
$s_icon1_f2->drawCurve(16, -271, -3,  -144);
$s_icon1_f2->drawCurve(-6, -237, -77, -168);
$s_icon1_f2->drawLine(-27, -51);
$s_icon1_f2->setLeftFill(0xc9, 0xc9, 0xc9);
$s_icon1_f2->drawCurve(-55, -223, -221, -72);
$s_icon1_f2->movePenTo(8530, 5051);
$s_icon1_f2->setLeftFill(0xff, 0xff, 0xff);
$s_icon1_f2->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1_f2->drawCurve(3,  -24, -7, 7);
$s_icon1_f2->drawCurve(-3, 3,   7,  14);
$s_icon1_f2->movePenTo(7606, 4781);
$s_icon1_f2->setLeftFill(0x01, 0x01, 0x01);
$s_icon1_f2->drawCurve(-2,  78,  143,  62);
$s_icon1_f2->drawCurve(101, 43,  112,  17);
$s_icon1_f2->drawCurve(30,  -58, 61,   -105);
$s_icon1_f2->drawCurve(51,  -93, 18,   -84);
$s_icon1_f2->drawCurve(-86, -31, -274, -69);
$s_icon1_f2->drawLine(-95, 113);
$s_icon1_f2->drawCurve(-55, 71, -4, 56);
$s_icon1_f2->movePenTo(7490, 4777);
$s_icon1_f2->drawCurve(104, -64, 12,  -88);
$s_icon1_f2->drawCurve(10,  -77, -62, -69);
$s_icon1_f2->drawCurve(-60, -66, -91, -19);
$s_icon1_f2->drawCurve(-98, -20, -82, 50);
$s_icon1_f2->drawCurve(-98, 58,  -2,  82);
$s_icon1_f2->drawCurve(-2,  71,  68,  67);
$s_icon1_f2->drawCurve(64,  63,  85,  24);
$s_icon1_f2->drawCurve(92,  26,  60,  -38);
$s_icon1_f2->movePenTo(7011, 4245);
$s_icon1_f2->drawCurve(-112, -53, -59,  12);
$s_icon1_f2->drawCurve(-118, 23,  -15,  111);
$s_icon1_f2->drawCurve(-14,  99,  66,   102);
$s_icon1_f2->drawCurve(68,   104, 90,   8);
$s_icon1_f2->drawCurve(106,  10,  80,   -140);
$s_icon1_f2->drawCurve(22,   -41, 55,   -159);
$s_icon1_f2->drawCurve(-17,  -5,  -152, -71);
$s_icon1_f2->movePenTo(7080, 4801);
$s_icon1_f2->drawCurve(-45, 19,  -66, 108);
$s_icon1_f2->drawCurve(-70, 113, 21,  40);
$s_icon1_f2->drawCurve(27,  56,  110, 23);
$s_icon1_f2->drawCurve(93,  18,  -10, 43);
$s_icon1_f2->drawLine(90, -13);
$s_icon1_f2->drawCurve(31, -11, 39, -56);
$s_icon1_f2->drawLine(68, -93);
$s_icon1_f2->drawCurve(42,   -62, -10,  -45);
$s_icon1_f2->drawCurve(-13,  -48, -129, -57);
$s_icon1_f2->drawCurve(-129, -57, -49,  22);
$s_icon1_f2->movePenTo(6900, 5274);
$s_icon1_f2->drawCurve(-70,  -13, -104, 114);
$s_icon1_f2->drawCurve(-103, 114, 20,   68);
$s_icon1_f2->drawCurve(24,   74,  140,  45);
$s_icon1_f2->drawCurve(139,  46,  64,   -41);
$s_icon1_f2->drawCurve(66,   -39, 42,   -103);
$s_icon1_f2->drawCurve(36,   -86, 6,    -92);
$s_icon1_f2->drawCurve(-41,  -9,  -90,  -37);
$s_icon1_f2->drawCurve(-83,  -34, -46,  -7);
$s_icon1_f2->movePenTo(7193, 5081);
$s_icon1_f2->setLeftFill(0xff, 0xff, 0xff);
$s_icon1_f2->setRightFill(0x01, 0x01, 0x01);
$s_icon1_f2->drawCurve(65, -18, 82, -142);
$s_icon1_f2->drawLine(-260, -80);
$s_icon1_f2->drawLine(-100, 200);
$s_icon1_f2->drawCurve(160, 55, 53, -15);
$s_icon1_f2->movePenTo(6552, 5178);
$s_icon1_f2->setLeftFill(0x01, 0x01, 0x01);
$s_icon1_f2->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1_f2->drawCurve(-74,  -72,  -198, -45);
$s_icon1_f2->drawCurve(-144, 202,  61,   112);
$s_icon1_f2->drawCurve(55,   100,  248,  66);
$s_icon1_f2->drawCurve(140,  -215, -58,  -109);
$s_icon1_f2->drawLine(-23, -32);
$s_icon1_f2->setRightFill(0xff, 0xff, 0xff);
$s_icon1_f2->drawCurve(-12,  47,   -67,  149);
$s_icon1_f2->drawCurve(-160, -16,  -120, -64);
$s_icon1_f2->drawCurve(41,   -157, 90,   -17);
$s_icon1_f2->drawCurve(63,   -12,  158,  63);
$s_icon1_f2->setLeftFill(0xc9, 0xc9, 0xc9);
$s_icon1_f2->drawLine(8,  3);
$s_icon1_f2->drawLine(-1, 4);
$s_icon1_f2->movePenTo(7186, 5661);
$s_icon1_f2->setLeftFill(0x01, 0x01, 0x01);
$s_icon1_f2->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1_f2->drawCurve(-43, 112, 89,  46);
$s_icon1_f2->drawCurve(77,  40,  124, -28);
$s_icon1_f2->drawCurve(125, -28, 52,  -72);
$s_icon1_f2->drawCurve(61,  -82, -88, -95);
$s_icon1_f2->drawCurve(-26, -28, -81, -32);
$s_icon1_f2->drawCurve(-93, -38, -23, -15);
$s_icon1_f2->drawCurve(-93, 85,  -27, 38);
$s_icon1_f2->drawCurve(-37, 55,  -17, 42);
$s_icon1_f2->movePenTo(7007, 5486);
$s_icon1_f2->setLeftFill(0xff, 0xff, 0xff);
$s_icon1_f2->setRightFill(0x01, 0x01, 0x01);
$s_icon1_f2->drawCurve(38, -69, 15, -56);
$s_icon1_f2->drawLine(-200, -60);
$s_icon1_f2->drawCurve(-125, 199, -15, 41);
$s_icon1_f2->drawLine(220, 60);
$s_icon1_f2->drawLine(67,  -115);
$s_icon1_f2->movePenTo(7826, 5260);
$s_icon1_f2->setLeftFill(0x01, 0x01, 0x01);
$s_icon1_f2->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1_f2->drawCurve(50,   -84, 4,    -115);
$s_icon1_f2->drawCurve(-223, -60, -72,  26);
$s_icon1_f2->drawCurve(-73,  26,  -132, 188);
$s_icon1_f2->drawCurve(72,   96,  89,   33);
$s_icon1_f2->drawCurve(83,   30,  77,   -31);
$s_icon1_f2->drawCurve(77,   -30, 48,   -79);
$s_icon1_f2->movePenTo(7796, 5107);
$s_icon1_f2->setLeftFill(0xff, 0xff, 0xff);
$s_icon1_f2->setRightFill(0x01, 0x01, 0x01);
$s_icon1_f2->drawCurve(-63,  -129, -129, 70);
$s_icon1_f2->drawCurve(-117, 63,   -27,  110);
$s_icon1_f2->drawLine(140, 53);
$s_icon1_f2->drawCurve(74, 20,  77,  -52);
$s_icon1_f2->drawCurve(82, -56, -37, -79);
$s_icon1_f2->movePenTo(7346, 6517);
$s_icon1_f2->drawCurve(-3, 20, 7, -10);
$s_icon1_f2->drawLine(2, -9);
$s_icon1_f2->drawCurve(-1, -8, -5, 7);
$s_icon1_f2->movePenTo(3904, 3788);
$s_icon1_f2->setLeftFill(0x01, 0x01, 0x01);
$s_icon1_f2->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1_f2->drawLine(-114, 173);
$s_icon1_f2->drawLine(-195, 276);
$s_icon1_f2->drawCurve(-125, 202, 23,  102);
$s_icon1_f2->drawCurve(17,   70,  131, 75);
$s_icon1_f2->drawLine(199, 95);
$s_icon1_f2->drawCurve(519, 256, 421, 97);
$s_icon1_f2->drawCurve(254, 61,  99,  -54);
$s_icon1_f2->drawCurve(66,  -38, 79,  -117);
$s_icon1_f2->drawLine(112, -185);
$s_icon1_f2->drawCurve(111,  -145, 56,   -85);
$s_icon1_f2->drawCurve(101,  -154, -8,   -106);
$s_icon1_f2->drawCurve(-6,   -78,  -78,  -50);
$s_icon1_f2->drawCurve(-31,  -20,  -115, -46);
$s_icon1_f2->drawCurve(-240, -90,  -480, -166);
$s_icon1_f2->drawCurve(-208, -66,  -252, -101);
$s_icon1_f2->drawCurve(-63,  -31,  -34,  -13);
$s_icon1_f2->drawCurve(-61,  -23,  -39,  17);
$s_icon1_f2->drawCurve(-61,  29,   -78,  115);
$s_icon1_f2->movePenTo(3956, 3921);
$s_icon1_f2->setLeftFill(0xff, 0xff, 0xff);
$s_icon1_f2->setRightFill(0x01, 0x01, 0x01);
$s_icon1_f2->drawCurve(-277, 390, -59, 210);
$s_icon1_f2->drawCurve(175,  97,  290, 110);
$s_icon1_f2->drawLine(475, 186);
$s_icon1_f2->drawCurve(345, 163, 131, -46);
$s_icon1_f2->drawCurve(56,  -21, 58,  -77);
$s_icon1_f2->drawLine(90, -126);
$s_icon1_f2->drawCurve(324, -399, -61,  -141);
$s_icon1_f2->drawCurve(-33, -74,  -242, -61);
$s_icon1_f2->drawLine(-328, -75);
$s_icon1_f2->drawCurve(-306, -105, -234, -108);
$s_icon1_f2->drawLine(-117, -59);
$s_icon1_f2->drawCurve(-75, -35, -45, 4);
$s_icon1_f2->drawCurve(-49, 4,   -49, 62);
$s_icon1_f2->drawCurve(-28, 34,  -41, 67);
$s_icon1_f2->movePenTo(5952, 4584);
$s_icon1_f2->setLeftFill(0x01, 0x01, 0x01);
$s_icon1_f2->setRightFill(0xc9, 0xc9, 0xc9);
$s_icon1_f2->drawCurve(-9,  69,  44,  70);
$s_icon1_f2->drawCurve(44,  68,  69,  31);
$s_icon1_f2->drawCurve(74,  33,  66,  -31);
$s_icon1_f2->drawCurve(118, -56, 23,  -85);
$s_icon1_f2->drawCurve(21,  -73, -53, -71);
$s_icon1_f2->drawCurve(-51, -68, -85, -24);
$s_icon1_f2->drawCurve(-92, -26, -78, 43);
$s_icon1_f2->drawCurve(-80, 43,  -11, 77);
$s_icon1_f2->movePenTo(6072, 5171);
$s_icon1_f2->drawCurve(45,  -105, -17, -85);
$s_icon1_f2->drawCurve(-98, 97,   -62, 123);
$s_icon1_f2->drawLine(-240, -100);
$s_icon1_f2->drawLine(39,   -87);
$s_icon1_f2->drawCurve(21, -52, 0, -41);
$s_icon1_f2->drawLine(-67, 83);
$s_icon1_f2->drawCurve(-38, 53,  -2,  40);
$s_icon1_f2->drawCurve(-5,  70,  113, 73);
$s_icon1_f2->drawCurve(106, 69,  69,  -8);
$s_icon1_f2->drawCurve(86,  -14, 50,  -116);
$s_icon1_f2->movePenTo(6104, 6526);
$s_icon1_f2->setLeftFill(0xff, 0xff, 0xff);
$s_icon1_f2->setRightFill();
$s_icon1_f2->drawLine(9, 1);
$s_icon1_f2->drawCurve(0, -20, -10, 10);
$s_icon1_f2->drawLine(1, 9);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(3686, 2737);
$s_icon1_f2->setLeftFill(0xec, 0xec, 0xec);
$s_icon1_f2->drawCurve(-3, 20, 7, -10);
$s_icon1_f2->drawLine(2, -9);
$s_icon1_f2->drawCurve(-1, -8, -5, 7);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(9280, 3701);
$s_icon1_f2->setLeftFill(0xff, 0xff, 0xff);
$s_icon1_f2->drawCurve(-21,  153, -121, 227);
$s_icon1_f2->drawCurve(-145, 250, -57,  110);
$s_icon1_f2->drawCurve(-382, 775, -301, 465);
$s_icon1_f2->drawCurve(-200, 310, -149, 131);
$s_icon1_f2->drawCurve(-214, 188, -290, 27);
$s_icon1_f2->drawCurve(-207, 22,  -262, -62);
$s_icon1_f2->drawCurve(-61,  -15, -390, -118);
$s_icon1_f2->drawLine(-1420, -477);
$s_icon1_f2->drawLine(-823,  -263);
$s_icon1_f2->drawCurve(-464, -160, -333, -167);
$s_icon1_f2->drawCurve(-552, -271, -68,  -425);
$s_icon1_f2->drawCurve(-58,  139,  79,   165);
$s_icon1_f2->drawCurve(68,   143,  131,  96);
$s_icon1_f2->drawCurve(229,  171,  372,  166);
$s_icon1_f2->drawCurve(213,  96,   426,  174);
$s_icon1_f2->drawCurve(1304, 565,  856,  225);
$s_icon1_f2->drawCurve(402,  106,  154,  20);
$s_icon1_f2->drawCurve(318,  41,   246,  -94);
$s_icon1_f2->drawCurve(344,  -134, 314,  -351);
$s_icon1_f2->drawCurve(175,  -197, 317,  -471);
$s_icon1_f2->drawLine(186, -263);
$s_icon1_f2->drawCurve(112, -158, 66,  -108);
$s_icon1_f2->drawCurve(189, -311, 40,  -280);
$s_icon1_f2->drawCurve(47,  -317, -70, -123);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(7013, 4251);
$s_icon1_f2->setLeftFill(0xec, 0xec, 0xec);
$s_icon1_f2->drawLine(10, 3);
$s_icon1_f2->drawCurve(10, -7, -20, 4);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(6780, 5021);
$s_icon1_f2->setLeftFill(0x01, 0x01, 0x01);
$s_icon1_f2->drawCurve(83,   -157, 3,    -80);
$s_icon1_f2->drawCurve(4,    -65,  -114, -42);
$s_icon1_f2->drawCurve(-142, -71,  -71,  38);
$s_icon1_f2->drawCurve(-89,  47,   -29,  96);
$s_icon1_f2->drawCurve(-32,  103,  87,   54);
$s_icon1_f2->drawCurve(56,   31,   64,   19);
$s_icon1_f2->drawCurve(76,   27,   104,  0);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(6460, 4861);
$s_icon1_f2->setLeftFill(0xff, 0x01, 0x01);
$s_icon1_f2->drawCurve(117,  56,   163, 44);
$s_icon1_f2->drawCurve(75,   -146, 25,  -74);
$s_icon1_f2->drawCurve(-150, -63,  -86, 14);
$s_icon1_f2->drawCurve(-115, 19,   -29, 150);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(6693, 4691);
$s_icon1_f2->setLeftFill(0xb8, 0x01, 0x01);
$s_icon1_f2->drawLine(10, 3);
$s_icon1_f2->drawCurve(10, -7, -20, 4);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(7093, 4791);
$s_icon1_f2->setLeftFill(0xec, 0xec, 0xec);
$s_icon1_f2->drawLine(10, 3);
$s_icon1_f2->drawCurve(10, -7, -20, 4);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(3890, 4811);
$s_icon1_f2->setLeftFill(0xec, 0xec, 0xec);
$s_icon1_f2->drawLine(16, 3);
$s_icon1_f2->drawCurve(7, -7, -23, 4);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(5780, 4921);
$s_icon1_f2->setLeftFill(0x01, 0x01, 0x01);
$s_icon1_f2->drawCurve(65, -21, 75, 18);
$s_icon1_f2->drawCurve(44, 11,  96, 32);
$s_icon1_f2->drawLine(-100, -100);
$s_icon1_f2->drawLine(-104, 3);
$s_icon1_f2->drawCurve(-48, 9, -28, 48);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(6033, 4931);
$s_icon1_f2->setLeftFill(0xec, 0xec, 0xec);
$s_icon1_f2->drawLine(10, 3);
$s_icon1_f2->drawCurve(10, -7, -20, 4);
$s_icon1_f2->movePenTo(6453, 4931);
$s_icon1_f2->drawLine(10, 3);
$s_icon1_f2->drawCurve(10, -7, -20, 4);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(5773, 5131);
$s_icon1_f2->setLeftFill(0xec, 0xec, 0xec);
$s_icon1_f2->drawLine(10, 3);
$s_icon1_f2->drawCurve(10, -7, -20, 4);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(4780, 5141);
$s_icon1_f2->setLeftFill(0xdd, 0xdd, 0xdd);
$s_icon1_f2->drawCurve(41,  20,  59,  0);
$s_icon1_f2->drawCurve(-40, -15, -60, -5);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(7120, 5181);
$s_icon1_f2->setLeftFill(0xec, 0xec, 0xec);
$s_icon1_f2->drawLine(0,   40);
$s_icon1_f2->drawLine(26,  -11);
$s_icon1_f2->drawLine(14,  -29);
$s_icon1_f2->drawLine(-40, 0);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(5913, 5311);
$s_icon1_f2->setLeftFill(0xec, 0xec, 0xec);
$s_icon1_f2->drawLine(10, 3);
$s_icon1_f2->drawCurve(10, -7, -20, 4);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(7260, 5721);
$s_icon1_f2->setLeftFill(0xff, 0xff, 0xff);
$s_icon1_f2->drawCurve(85,  31,  31,  2);
$s_icon1_f2->drawCurve(78,  7,   26,  -59);
$s_icon1_f2->drawCurve(23,  -51, -26, -61);
$s_icon1_f2->drawCurve(-27, -62, -49, -2);
$s_icon1_f2->drawCurve(-57, -1,  -54, 96);
$s_icon1_f2->drawCurve(-17, 29,  -13, 71);
$s_icon1_f2->setLeftFill();
$s_icon1_f2->setRightFill();

$s_icon1_f2->movePenTo(1674, 4251);
$s_icon1_f2->setLeftFill(0xab, 0x97, 0xfd);
$s_icon1_f2->setRightFill(0x00, 0x00, 0x00);
$s_icon1_f2->drawCurve(-18,  232,  61,   198);
$s_icon1_f2->drawCurve(36,   126,  92,   66);
$s_icon1_f2->drawCurve(101,  76,   118,  -47);
$s_icon1_f2->drawCurve(222,  -87,  31,   -290);
$s_icon1_f2->drawCurve(10,   -81,  -2,   -170);
$s_icon1_f2->drawCurve(-2,   -164, 8,    -72);
$s_icon1_f2->drawCurve(30,   -256, 154,  -321);
$s_icon1_f2->drawCurve(32,   -65,  259,  -473);
$s_icon1_f2->drawCurve(89,   -161, 41,   -59);
$s_icon1_f2->drawCurve(89,   -127, 94,   -46);
$s_icon1_f2->drawCurve(-55,  203,  -200, 330);
$s_icon1_f2->drawCurve(-216, 353,  -58,  174);
$s_icon1_f2->drawCurve(-63,  180,  -15,  385);
$s_icon1_f2->drawCurve(-15,  376,  -65,  176);
$s_icon1_f2->drawCurve(-29,  75,   -89,  94);
$s_icon1_f2->drawCurve(-109, 118,  -27,  39);
$s_icon1_f2->drawCurve(166,  61,   76,   14);
$s_icon1_f2->drawCurve(133,  22,   111,  -53);
$s_icon1_f2->drawCurve(95,   -47,  84,   -104);
$s_icon1_f2->drawCurve(82,   -103, 41,   -123);
$s_icon1_f2->drawCurve(99,   -294, -179, -157);
$s_icon1_f2->drawCurve(-79,  292,  -252, 95);
$s_icon1_f2->drawCurve(4,    -65,  55,   -114);
$s_icon1_f2->drawCurve(56,   -117, 8,    -64);
$s_icon1_f2->drawCurve(5,    -45,  -11,  -109);
$s_icon1_f2->drawCurve(-9,   -99,  13,   -59);
$s_icon1_f2->drawCurve(42,   -224, 150,  -278);
$s_icon1_f2->drawCurve(86,   -160, 189,  -310);
$s_icon1_f2->drawLine(182, -306);
$s_icon1_f2->drawCurve(115, -169, 126, -87);
$s_icon1_f2->drawCurve(95,  -69,  136, -19);
$s_icon1_f2->drawLine(240, -14);
$s_icon1_f2->drawCurve(146,  -2,   82,   -26);
$s_icon1_f2->drawCurve(116,  -37,  78,   -98);
$s_icon1_f2->drawCurve(241,  -304, 17,   -285);
$s_icon1_f2->drawCurve(20,   -341, -333, -182);
$s_icon1_f2->drawCurve(-107, -60,  -144, -8);
$s_icon1_f2->drawCurve(43,   246,  -66,  207);
$s_icon1_f2->drawCurve(-59,  180,  -164, 212);
$s_icon1_f2->drawLine(-39, -10);
$s_icon1_f2->drawCurve(5, -69, 42, -95);
$s_icon1_f2->drawLine(77, -164);
$s_icon1_f2->drawCurve(95,   -205, -56,  -141);
$s_icon1_f2->drawCurve(-119, -303, -342, 195);
$s_icon1_f2->drawCurve(-200, 112,  -247, 263);
$s_icon1_f2->drawCurve(-252, 275,  -240, 406);
$s_icon1_f2->drawCurve(-66,  115,  -332, 623);
$s_icon1_f2->drawCurve(-180, 321,  -85,  163);
$s_icon1_f2->drawCurve(-148, 282,  -62,  224);
$s_icon1_f2->drawCurve(-70,  259,  -14,  170);
$s_icon1_f2->movePenTo(1610, 4252);
$s_icon1_f2->setLeftFill(0x00, 0x00, 0x00);
$s_icon1_f2->setRightFill();
$s_icon1_f2->drawCurve(-16, 242,  71,   209);
$s_icon1_f2->drawCurve(41,  133,  104,  72);
$s_icon1_f2->drawCurve(45,  31,   171,  72);
$s_icon1_f2->drawCurve(159, 66,   -2,   3);
$s_icon1_f2->drawCurve(191, 70,   76,   13);
$s_icon1_f2->drawCurve(148, 24,   122,  -53);
$s_icon1_f2->drawCurve(105, -48,  91,   -107);
$s_icon1_f2->drawCurve(87,  -103, 46,   -132);
$s_icon1_f2->drawCurve(116, -339, -213, -135);
$s_icon1_f2->drawCurve(-93, -59,  -91,  -26);
$s_icon1_f2->drawCurve(-51, -12,  -7,   -64);
$s_icon1_f2->drawCurve(-9,  -79,  11,   -59);
$s_icon1_f2->drawCurve(18,  -98,  86,   -148);
$s_icon1_f2->drawLine(161, -268);
$s_icon1_f2->drawCurve(100, -178, 193, -304);
$s_icon1_f2->drawCurve(83,  -140, 130, -173);
$s_icon1_f2->drawCurve(145, -193, 65,  -40);
$s_icon1_f2->drawCurve(42,  -30,  209, -3);
$s_icon1_f2->drawLine(351, 2);
$s_icon1_f2->drawCurve(56,   0,    138,  -119);
$s_icon1_f2->drawCurve(107,  -94,  64,   -77);
$s_icon1_f2->drawCurve(262,  -315, 13,   -299);
$s_icon1_f2->drawCurve(7,    -151, -260, -172);
$s_icon1_f2->drawCurve(-123, -80,  -176, -79);
$s_icon1_f2->drawCurve(-500, -222, -227, 120);
$s_icon1_f2->drawCurve(-219, 115,  -269, 273);
$s_icon1_f2->drawCurve(-277, 286,  -257, 419);
$s_icon1_f2->drawCurve(-88,  149,  -340, 619);
$s_icon1_f2->drawCurve(-182, 312,  -105, 191);
$s_icon1_f2->drawCurve(-159, 295,  -65,  233);
$s_icon1_f2->drawCurve(-73,  273,  -12,  177);

# Icon1 Movieclip
my $i_icon1 = new SWF::Sprite();
$item = $i_icon1->add($s_icon1);
$item->scaleTo(0.5);
$item->moveTo(-3000, -2100);
$i_icon1->nextFrame();
$i_icon1->remove($item);
$item = $i_icon1->add($s_icon1_f2);
$item->scaleTo(0.5);
$item->moveTo(-3000, -2100);
$i_icon1->nextFrame();


$envelope = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(8722, 1745);
$s1->setLeftFill(0x00, 0x00, 0x00);
$s1->drawCurve(-7, -42, 6, -35);
$s1->drawCurve(3, -14, -68, -18);
$s1->drawLine(-40, -15);
$s1->drawLine(0, -2);
$s1->drawLine(-5, -2);
$s1->drawCurve(-11, -9, 7, -9);
$s1->drawLine(10, -12);
$s1->drawLine(-7, 7);
$s1->drawLine(-15, 17);
$s1->drawCurve(-372, -133, -468, -40);
$s1->drawCurve(-301, -26, -578, 7);
$s1->drawCurve(-1570, 19, -1710, 381);
$s1->drawCurve(-751, 162, -409, 194);
$s1->drawCurve(-136, 59, -73, 40);
$s1->drawCurve(-132, 72, -29, 76);
$s1->drawCurve(-41, 106, 107, 251);
$s1->drawLine(165, 376);
$s1->drawCurve(96, 223, 43, 161);
$s1->drawCurve(153, 581, 43, 714);
$s1->drawCurve(21, 339, 3, 741);
$s1->drawLine(-1, 0);
$s1->drawCurve(-14, 37, 15, 145);
$s1->drawLine(0, 43);
$s1->drawLine(4, -4);
$s1->drawCurve(24, 194, 71, 65);
$s1->drawCurve(34, 31, 27, 39);
$s1->setRightFill(0xff, 0xff, 0xff);
$s1->drawCurve(8, -74, 37, -92);
$s1->drawCurve(30, -75, 49, -88);
$s1->drawLine(78, -126);
$s1->drawLine(110, -195);
$s1->drawCurve(252, -493, 132, -246);
$s1->drawCurve(226, -424, 193, -297);
$s1->drawCurve(114, -212, 78, -119);
$s1->drawCurve(143, -219, 129, -17);
$s1->drawCurve(81, -12, 149, 47);
$s1->drawLine(230, 75);
$s1->drawLine(641, 180);
$s1->drawCurve(397, 96, 262, -36);
$s1->drawCurve(153, -20, 215, -136);
$s1->drawCurve(121, -76, 211, -165);
$s1->drawLine(128, -121);
$s1->drawCurve(88, -78, 64, 6);
$s1->drawCurve(89, 6, 125, 95);
$s1->drawLine(186, 156);
$s1->drawCurve(272, 221, 181, 245);
$s1->drawCurve(167, 227, 153, 337);
$s1->drawCurve(25, 55, 109, 192);
$s1->drawLine(14, 29);
$s1->drawCurve(37, 75, 14, 55);
$s1->setRightFill(0x33, 0x33, 0x33);
$s1->drawLine(62, -139);
$s1->setRightFill();
$s1->drawLine(8, -17);
$s1->drawLine(2, 16);
$s1->setRightFill(0x33, 0x33, 0x33);
$s1->drawLine(12, 0);
$s1->setRightFill(0xff, 0xff, 0xff);
$s1->drawLine(-8, -29);
$s1->drawLine(-14, -43);
$s1->drawCurve(-175, -524, -586, -670);
$s1->drawLine(-250, -216);
$s1->drawCurve(-173, -149, -24, -95);
$s1->drawCurve(-16, -68, 116, -105);
$s1->drawCurve(62, -56, 112, -81);
$s1->drawCurve(343, -294, 317, -316);
$s1->drawLine(115, -125);
$s1->drawLine(42, -49);
$s1->drawCurve(75, -80, 67, -43);
$s1->drawCurve(21, -13, 20, -10);
$s1->drawCurve(168, 451, 15, 659);
$s1->drawCurve(8, 339, -51, 791);
$s1->drawCurve(-26, 317, -13, 83);
$s1->drawCurve(-41, 252, -81, 74);
$s1->setRightFill(0x33, 0x33, 0x33);
$s1->drawCurve(38, 1, 5, 9);
$s1->drawCurve(12, 23, -21, 49);
$s1->setRightFill();
$s1->drawLine(63, -55);
$s1->drawLine(27, -80);
$s1->drawCurve(82, -72, 40, -254);
$s1->drawCurve(13, -82, 26, -318);
$s1->drawCurve(51, -773, -8, -357);
$s1->drawCurve(-8, -325, -63, -346);
$s1->drawLine(-53, -306);
$s1->drawCurve(-30, -156, -36, -97);
$s1->drawLine(4, -26);
$s1->drawCurve(7, -40, -11, -42);
$s1->drawLine(-20, -64);
$s1->setRightFill(0xff, 0xff, 0xff);
$s1->drawCurve(-25, 107, -69, 111);
$s1->drawCurve(-78, 127, -128, 131);
$s1->drawLine(-104, 100);
$s1->drawCurve(-112, 101, -201, 160);
$s1->drawLine(-276, 228);
$s1->drawCurve(-104, 93, -224, 227);
$s1->drawCurve(-209, 214, -128, 117);
$s1->drawCurve(-409, 376, -366, 113);
$s1->drawCurve(-200, 62, -323, -54);
$s1->drawLine(-517, -118);
$s1->drawCurve(-873, -199, -527, -258);
$s1->drawCurve(-115, -55, -627, -345);
$s1->drawCurve(-340, -188, -265, -110);
$s1->drawLine(-153, -58);
$s1->drawCurve(28, 111, 153, 92);
$s1->drawLine(136, 74);
$s1->drawLine(123, 59);
$s1->drawLine(696, 405);
$s1->drawCurve(418, 225, 326, 74);
$s1->drawCurve(-35, 163, -200, 233);
$s1->drawLine(-165, 187);
$s1->drawCurve(-91, 102, -49, 75);
$s1->drawCurve(-253, 386, -404, 834);
$s1->drawCurve(-13, 26, -101, 276);
$s1->drawLine(-1, 5);
$s1->drawCurve(-37, 98, -38, 70);
$s1->drawCurve(-37, 66, -36, 39);
$s1->drawLine(-1, -316);
$s1->drawCurve(14, -791, -13, -211);
$s1->drawCurve(-45, -735, -141, -536);
$s1->drawCurve(-72, -266, -52, -121);
$s1->drawCurve(-167, -318, -35, -85);
$s1->drawCurve(-109, -256, 31, -82);
$s1->drawCurve(28, -76, 133, -72);
$s1->drawCurve(77, -42, 132, -57);
$s1->drawCurve(412, -195, 748, -161);
$s1->drawCurve(1708, -380, 1572, -20);
$s1->drawCurve(567, -7, 312, 26);
$s1->drawCurve(386, 33, 319, 96);
$s1->drawLine(139, 46);
$s1->setLeftFill();
$s1->drawLine(17, 6);
$s1->drawLine(-7, 38);
$s1->movePenTo(8579, 5100);
$s1->setLeftFill(0x33, 0x33, 0x33);
$s1->setRightFill();
$s1->drawLine(-10, 1);
$s1->movePenTo(8614, 5100);
$s1->setRightFill(0xff, 0xff, 0xff);
$s1->drawLine(-19, 14);
$s1->drawLine(-4, -14);
$s1->movePenTo(8648, 5182);
$s1->setLeftFill();
$s1->setRightFill(0x33, 0x33, 0x33);
$s1->drawLine(-30, 53);
$s1->drawCurve(-59, 95, 5, 18);
$s1->drawCurve(15, 55, -11, 35);
$s1->drawCurve(-17, 57, -122, 37);
$s1->drawCurve(-141, 33, -36, 13);
$s1->drawCurve(-315, 111, -665, 189);
$s1->drawCurve(-916, 264, -1272, 173);
$s1->drawLine(-1105, 136);
$s1->drawLine(-1075, 127);
$s1->drawLine(-32, 4);
$s1->drawLine(0, -4);
$s1->drawCurve(-2, -19, -25, -47);
$s1->drawLine(-23, -39);
$s1->setRightFill(0xf0, 0xf0, 0xf0);
$s1->drawLine(-4, 1);
$s1->drawLine(-3, 0);
$s1->drawLine(0, -1);
$s1->drawLine(1, -9);
$s1->setLeftFill(0xff, 0xff, 0xff);
$s1->drawLine(6, 9);
$s1->setRightFill(0x33, 0x33, 0x33);
$s1->drawLine(1100, -130);
$s1->drawLine(1105, -136);
$s1->drawCurve(1272, -174, 916, -263);
$s1->drawCurve(673, -192, 307, -108);
$s1->drawLine(177, -46);
$s1->drawCurve(123, -38, 16, -56);
$s1->drawCurve(11, -33, -15, -57);
$i1 = $envelope->add($s1);
$i1->scaleTo(0.5);
$i1->moveTo(-2620, -1800);
$envelope->nextFrame();  # end of frame 1



$telefono3 = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(7575, 1918);
$s1->setRightFill(0x99, 0x99, 0x99, 0x00);
$s1->drawCurve(534, 752, 0, 980);
$s1->drawCurve(0, 1239, -855, 875);
$s1->drawCurve(-855, 876, -1210, 0);
$s1->drawCurve(-966, 0, -739, -558);
$s1->drawCurve(-187, -141, -173, -177);
$s1->drawCurve(-855, -875, 0, -1239);
$s1->drawCurve(0, -1239, 855, -876);
$s1->drawCurve(855, -875, 1210, 0);
$s1->drawCurve(1210, 0, 855, 875);
$s1->drawCurve(179, 183, 142, 200);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(8001, 3420);
$s1->setLeftFill(0x00, 0x00, 0x00);
$s1->drawCurve(98, 665, -296, 644);
$s1->drawCurve(-342, 737, -687, 446);
$s1->drawCurve(-697, 454, -805, 0);
$s1->drawCurve(-673, 0, -404, -219);
$s1->drawCurve(-527, -286, 140, -586);
$s1->drawCurve(530, 95, 272, -641);
$s1->drawCurve(76, -198, -14, -463);
$s1->drawCurve(-14, -433, 136, -269);
$s1->drawCurve(129, -249, 220, -249);
$s1->drawCurve(125, -143, 255, -259);
$s1->drawCurve(443, -478, 73, -532);
$s1->drawCurve(-966, 127, -641, 1138);
$s1->drawCurve(-478, 839, -205, 1169);
$s1->drawCurve(-32, 164, -83, 320);
$s1->drawCurve(-67, 280, 0, 170);
$s1->drawCurve(4, 478, 516, 235);
$s1->drawCurve(1119, 518, 1052, -426);
$s1->drawCurve(949, -385, 557, -982);
$s1->drawCurve(551, -975, -132, -1040);
$s1->drawCurve(-150, -1142, -948, -716);
$s1->drawCurve(-558, -419, -628, -103);
$s1->drawCurve(-1174, -184, -1011, 549);
$s1->drawCurve(-1056, 576, -314, 1105);
$s1->drawCurve(-188, 709, 73, 777);
$s1->drawCurve(97, 1050, 645, -13);
$s1->drawCurve(-505, -907, -42, -730);
$s1->drawCurve(-38, -726, 359, -627);
$s1->drawCurve(338, -587, 606, -375);
$s1->drawCurve(603, -371, 701, -65);
$s1->drawCurve(736, -68, 658, 297);
$s1->drawCurve(677, 300, 428, 644);
$s1->drawCurve(391, 590, 118, 770);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(6224, 1529);
$s1->setLeftFill(0x00, 0x00, 0x00);
$s1->drawLine(-419, 1023);
$s1->drawCurve(356, -52, 139, -341);
$s1->drawCurve(140, -340, -216, -290);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(4920, 4347);
$s1->setLeftFill(0x00, 0x00, 0x00);
$s1->drawLine(-558, 954);
$s1->drawCurve(397, -27, 213, -300);
$s1->drawCurve(226, -324, -278, -303);
$i4 = $telefono3->add($s1);
$i4->scaleTo(0.5);
$i4->moveTo(-2620, -1800);
$telefono3->nextFrame();  # end of frame 1


### Shape 1 ###

$telefono4 = new SWF::Sprite();
$s1 = new SWF::Shape();
$s1->movePenTo(8338, 3364);
$s1->setLeftFill(0x00, 0x00, 0x00);
$s1->drawCurve(301, -19, 150, -103);
$s1->drawCurve(138, -96, 48, -167);
$s1->drawCurve(44, -155, -37, -181);
$s1->drawCurve(-36, -176, -99, -146);
$s1->drawCurve(-51, -75, -139, -135);
$s1->drawCurve(-141, -134, -79, -50);
$s1->drawCurve(-106, -67, -122, -53);
$s1->drawCurve(-49, -21, -58, -15);
$s1->drawLine(-5230, 0);
$s1->drawCurve(-275, 97, -202, 205);
$s1->drawCurve(-172, 171, -152, 382);
$s1->drawLine(-45, 91);
$s1->drawCurve(-27, 54, 0, 43);
$s1->drawCurve(-3, 172, 66, 126);
$s1->drawCurve(78, 149, 155, 31);
$s1->drawCurve(190, 38, 174, 9);
$s1->setRightFill(0x00, 0x33, 0x99, 0x00);
$s1->drawLine(298, -10);
$s1->drawCurve(414, -51, 126, -252);
$s1->drawCurve(33, -66, 11, -101);
$s1->drawLine(11, -180);
$s1->drawCurve(15, -225, 114, -90);
$s1->drawCurve(86, -70, 209, -15);
$s1->drawLine(170, -9);
$s1->drawLine(149, -15);
$s1->drawLine(0, 587);
$s1->drawLine(2285, 0);
$s1->drawLine(0, -616);
$s1->drawLine(352, 24);
$s1->drawCurve(221, 21, 84, 114);
$s1->drawCurve(66, 90, 43, 170);
$s1->drawLine(67, 299);
$s1->drawCurve(44, 168, 71, 89);
$s1->drawCurve(91, 113, 161, 23);
$s1->drawCurve(281, 39, 239, -10);
$s1->drawLine(38, -2);
$s1->setLeftFill();
$s1->setLine(20, 0xcc, 0xcc, 0xcc, 0x00);
$s1->drawLine(0, 1760);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawCurve(75, 104, 58, 98);
$s1->drawCurve(96, 167, 40, 101);
$s1->drawCurve(63, 159, -11, 130);
$s1->drawCurve(-15, 199, -132, 16);
$s1->drawLine(14, 28);
$s1->drawLine(-314, 95);
$s1->drawLine(-486, 19);
$s1->drawLine(-4671, 0);
$s1->drawLine(-514, -85);
$s1->drawLine(-114, -74);
$s1->drawCurve(-68, -45, -28, -53);
$s1->drawCurve(-97, -173, 100, -266);
$s1->drawCurve(59, -160, 159, -243);
$s1->drawCurve(45, -68, 62, -74);
$s1->setRightFill(0x00, 0x33, 0x99, 0x00);
$s1->setLine(20, 0xcc, 0xcc, 0xcc, 0x00);
$s1->drawLine(0, -1660);
$s1->movePenTo(8338, 5124);
$s1->setLeftFill(0x00, 0x00, 0x00);
$s1->drawCurve(-149, -207, -220, -232);
$s1->drawCurve(-388, -388, -184, -199);
$s1->drawLine(-246, -288);
$s1->drawCurve(-161, -182, -121, -58);
$s1->drawLine(128, 179);
$s1->drawCurve(73, 106, 35, 86);
$s1->drawCurve(117, 301, -82, 325);
$s1->drawCurve(-78, 311, -232, 236);
$s1->drawLine(-165, 168);
$s1->drawCurve(-113, 100, -83, 16);
$s1->drawLine(-14, 43);
$s1->drawCurve(-57, -5, -95, 42);
$s1->drawLine(-148, 65);
$s1->drawCurve(-239, 83, -276, 26);
$s1->drawCurve(-304, 29, -309, -82);
$s1->drawCurve(-308, -81, -253, -176);
$s1->drawCurve(-263, -183, -157, -251);
$s1->drawCurve(-168, -269, -21, -312);
$s1->drawCurve(-16, -209, 190, -362);
$s1->drawLine(77, -125);
$s1->drawCurve(48, -84, -1, -48);
$s1->drawLine(-1029, 1028);
$s1->drawLine(-302, 293);
$s1->drawCurve(-94, 94, -71, 85);
$s1->movePenTo(6484, 5069);
$s1->setLeftFill(0x00, 0x33, 0x99, 0x00);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawLine(-15, 13);
$s1->drawCurve(-400, 316, -564, 0);
$s1->drawCurve(-438, 0, -339, -190);
$s1->drawCurve(-98, -55, -90, -71);
$s1->drawLine(-15, -13);
$s1->drawCurve(-385, -312, 1, -437);
$s1->drawCurve(-1, -447, 400, -316);
$s1->drawCurve(400, -316, 565, 0);
$s1->drawCurve(550, 0, 393, 301);
$s1->drawLine(21, 15);
$s1->drawCurve(400, 316, 0, 447);
$s1->drawCurve(-1, 437, -384, 312);
$s1->movePenTo(5501, 1760);
$s1->setLeftFill(0x00, 0x00, 0x00);
$s1->setRightFill();
$s1->drawLine(-4, 0);
$s1->drawLine(0, 6);
$s1->drawLine(4, -6);
$i1 = $telefono4->add($s1);
$i1->scaleTo(0.5);
$i1->moveTo(-2720, -1890);
$telefono4->nextFrame();  # end of frame 1
$telefono4->remove($i1);

### Shape 2 ###
$s2 = new SWF::Shape();
$s2->movePenTo(8338, 5128);
$s2->setLeftFill(0x00, 0x00, 0x00);
$s2->setRightFill(0x00, 0x33, 0x99, 0x00);
$s2->drawCurve(-151, -210, -223, -236);
$s2->drawCurve(-388, -388, -184, -199);
$s2->drawLine(-246, -288);
$s2->drawCurve(-161, -182, -121, -58);
$s2->drawLine(128, 179);
$s2->drawCurve(73, 106, 35, 86);
$s2->drawCurve(117, 301, -82, 325);
$s2->drawCurve(-78, 311, -232, 236);
$s2->drawLine(-165, 168);
$s2->drawCurve(-113, 100, -83, 16);
$s2->drawLine(-14, 43);
$s2->drawCurve(-57, -5, -95, 42);
$s2->drawLine(-148, 65);
$s2->drawCurve(-239, 83, -276, 26);
$s2->drawCurve(-304, 29, -309, -82);
$s2->drawCurve(-308, -81, -253, -176);
$s2->drawCurve(-263, -183, -157, -251);
$s2->drawCurve(-168, -269, -21, -312);
$s2->drawCurve(-16, -209, 190, -362);
$s2->drawLine(77, -125);
$s2->drawCurve(48, -84, -1, -48);
$s2->drawLine(-1029, 1028);
$s2->drawLine(-302, 293);
$s2->drawCurve(-91, 90, -69, 83);
$s2->setRightFill();
$s2->drawCurve(-65, 77, -47, 71);
$s2->drawCurve(-159, 243, -59, 160);
$s2->drawCurve(-100, 266, 97, 173);
$s2->drawCurve(28, 53, 68, 45);
$s2->drawLine(114, 74);
$s2->drawLine(514, 85);
$s2->drawLine(4671, 0);
$s2->drawLine(486, -19);
$s2->drawLine(314, -95);
$s2->drawLine(-14, -28);
$s2->drawCurve(132, -16, 15, -199);
$s2->drawCurve(11, -130, -63, -159);
$s2->drawCurve(-40, -101, -96, -167);
$s2->drawCurve(-56, -94, -72, -101);
$s2->setLeftFill(0x00, 0x33, 0x99, 0x00);
$s2->drawLine(0, -3048);
$s2->drawLine(-2669, 0);
$s2->setRightFill(0x00, 0x00, 0x00);
$s2->drawLine(-162, 131);
$s2->drawCurve(-130, 97, -144, -15);
$s2->drawCurve(-113, -12, -149, -87);
$s2->drawLine(-178, -114);
$s2->setRightFill();
$s2->drawLine(-688, 0);
$s2->setRightFill(0x00, 0x00, 0x00);
$s2->drawCurve(-154, 146, -75, 52);
$s2->drawLine(436, 433);
$s2->drawLine(-1609, 1617);
$s2->drawLine(-44, -44);
$s2->setRightFill();
$s2->drawLine(0, 706);
$s2->movePenTo(5669, 2080);
$s2->setLeftFill(0x00, 0x00, 0x00);
$s2->drawCurve(124, -108, 101, -109);
$s2->drawLine(25, -28);
$s2->drawCurve(199, -226, 32, -179);
$s2->drawCurve(29, -165, -84, -152);
$s2->drawCurve(-79, -140, -154, -101);
$s2->drawCurve(-149, -99, -174, -32);
$s2->drawLine(-282, -13);
$s2->drawCurve(-194, 4, -91, 21);
$s2->drawCurve(-122, 28, -124, 49);
$s2->drawCurve(-49, 20, -52, 31);
$s2->drawLine(-1193, 1199);
$s2->setRightFill(0x00, 0x33, 0x99, 0x00);
$s2->drawLine(-773, 776);
$s2->setRightFill();
$s2->drawLine(-1715, 1724);
$s2->drawCurve(-125, 263, 3, 288);
$s2->drawCurve(0, 241, 162, 377);
$s2->drawLine(33, 96);
$s2->drawCurve(20, 56, 30, 31);
$s2->drawCurve(120, 123, 135, 43);
$s2->drawCurve(161, 49, 131, -88);
$s2->drawCurve(161, -108, 128, -116);
$s2->drawCurve(115, -106, 88, -112);
$s2->drawCurve(255, -328, -89, -268);
$s2->drawCurve(-24, -69, -64, -79);
$s2->drawLine(-119, -135);
$s2->drawCurve(-149, -169, 16, -143);
$s2->drawCurve(12, -110, 136, -159);
$s2->drawLine(113, -126);
$s2->drawLine(95, -116);
$s2->drawLine(371, 369);
$s2->movePenTo(4105, 2080);
$s2->drawLine(36, -34);
$s2->drawCurve(170, -142, 140, 21);
$s2->drawCurve(110, 16, 151, 90);
$s2->drawLine(81, 49);
$s2->movePenTo(6464, 3554);
$s2->setLeftFill(0x00, 0x33, 0x99, 0x00);
$s2->setRightFill(0x00, 0x00, 0x00);
$s2->drawCurve(400, 316, 0, 447);
$s2->drawCurve(-1, 437, -384, 312);
$s2->drawLine(-15, 13);
$s2->drawCurve(-400, 316, -564, 0);
$s2->drawCurve(-438, 0, -339, -190);
$s2->drawCurve(-98, -55, -90, -71);
$s2->drawLine(-15, -13);
$s2->drawCurve(-385, -312, 1, -437);
$s2->drawCurve(-1, -447, 400, -316);
$s2->drawCurve(400, -316, 565, 0);
$s2->drawCurve(550, 0, 393, 301);
$s2->drawLine(21, 15);
$s2->movePenTo(2659, 2856);
$s2->setLeftFill();
$s2->setRightFill(0x00, 0x33, 0x99, 0x00);
$s2->drawLine(0, -776);
$s2->drawLine(773, 0);
$i1 = $telefono4->add($s2);
$i1->scaleTo(0.5);
$i1->moveTo(-2720, -1890);
$telefono4->nextFrame();  # end of frame 2
$telefono4->remove($i1);



$conference1 = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(7708, 2179);
$s1->setLeftFill(0x06, 0x04, 0x07);
$s1->drawCurve(-94, -8, -225, 66);
$s1->drawCurve(-209, 62, -92, -23);
$s1->drawCurve(-122, -31, -248, -183);
$s1->drawCurve(-230, -168, -160, -18);
$s1->drawCurve(0, -285, -17, -118);
$s1->drawCurve(-34, -233, -116, -138);
$s1->drawCurve(-43, -50, -111, -68);
$s1->drawCurve(-110, -66, -39, -49);
$s1->drawCurve(-30, -36, -17, -78);
$s1->drawCurve(-18, -84, -22, -32);
$s1->drawCurve(-65, -97, -133, -15);
$s1->drawCurve(-127, -15, -95, 67);
$s1->drawCurve(-65, 46, -27, 104);
$s1->drawCurve(-32, 117, -33, 36);
$s1->drawCurve(-47, 51, -73, 40);
$s1->drawLine(-124, 62);
$s1->drawCurve(-146, 77, -43, 144);
$s1->drawCurve(-20, 66, 3, 224);
$s1->drawCurve(2, 199, -42, 67);
$s1->drawCurve(-25, 42, -64, 28);
$s1->drawLine(-107, 40);
$s1->drawCurve(-123, 57, -237, 157);
$s1->drawCurve(-84, -177, -230, -160);
$s1->drawCurve(-244, -171, -183, 21);
$s1->drawCurve(-68, 7, -117, 64);
$s1->drawCurve(-116, 64, -59, 5);
$s1->drawCurve(-59, 4, -105, -25);
$s1->drawCurve(-120, -29, -39, -3);
$s1->drawCurve(-172, -12, -35, 232);
$s1->drawCurve(-14, 83, 57, 86);
$s1->drawCurve(80, 121, 10, 30);
$s1->drawCurve(15, 49, -10, 100);
$s1->drawCurve(-10, 110, 5, 41);
$s1->drawCurve(23, 212, 177, 208);
$s1->drawCurve(32, 37, 181, 138);
$s1->drawCurve(148, 113, 29, 75);
$s1->drawCurve(22, 54, -39, 125);
$s1->drawLine(-56, 178);
$s1->drawCurve(-35, 154, -5, 205);
$s1->drawCurve(-3, 119, 3, 242);
$s1->drawCurve(-314, 56, -182, 207);
$s1->drawCurve(-200, 225, 76, 292);
$s1->drawCurve(-84, 43, -60, 82);
$s1->drawCurve(-59, 81, -12, 89);
$s1->drawCurve(-30, 211, 225, 64);
$s1->drawLine(139, -6);
$s1->drawCurve(92, -17, 42, 19);
$s1->drawCurve(61, 27, 65, 48);
$s1->drawLine(105, 80);
$s1->drawCurve(124, 86, 152, -8);
$s1->drawCurve(78, -3, 205, -89);
$s1->drawCurve(188, -83, 79, 12);
$s1->drawCurve(59, 8, 93, 81);
$s1->drawLine(139, 120);
$s1->drawCurve(82, 60, 219, 94);
$s1->drawCurve(206, 90, 86, 70);
$s1->drawCurve(54, 47, 9, 169);
$s1->drawCurve(11, 214, 9, 30);
$s1->drawCurve(47, 153, 129, 76);
$s1->drawLine(117, 63);
$s1->drawCurve(73, 40, 51, 44);
$s1->drawCurve(34, 30, 27, 51);
$s1->drawLine(41, 84);
$s1->drawCurve(50, 93, 115, 6);
$s1->drawCurve(147, 10, 58, -77);
$s1->drawCurve(56, -112, 69, -65);
$s1->drawCurve(28, -29, 72, -20);
$s1->drawLine(110, -37);
$s1->drawCurve(164, -85, 96, -225);
$s1->drawCurve(17, -41, 15, -81);
$s1->drawLine(28, -142);
$s1->drawCurve(37, -176, 83, -44);
$s1->drawCurve(34, -19, 208, -99);
$s1->drawCurve(148, -71, 90, -61);
$s1->drawLine(112, -88);
$s1->drawCurve(76, -58, 52, -3);
$s1->drawCurve(41, -5, 54, 41);
$s1->drawLine(85, 70);
$s1->drawCurve(145, 112, 195, 58);
$s1->drawCurve(237, 76, 199, -119);
$s1->drawLine(90, -74);
$s1->drawCurve(56, -50, 41, -10);
$s1->drawLine(125, 10);
$s1->drawCurve(78, 16, 50, -26);
$s1->drawCurve(176, -86, -36, -142);
$s1->drawCurve(-9, -35, -41, -94);
$s1->drawCurve(-38, -86, -8, -52);
$s1->drawCurve(-7, -47, 12, -110);
$s1->drawCurve(11, -102, -16, -61);
$s1->drawCurve(-108, -386, -592, -254);
$s1->drawCurve(44, -191, -8, -349);
$s1->drawLine(-12, -119);
$s1->drawCurve(-6, -76, 22, -39);
$s1->drawCurve(42, -77, 165, -101);
$s1->drawCurve(167, -102, 49, -86);
$s1->drawCurve(55, -93, -7, -182);
$s1->drawCurve(-6, -190, 35, -72);
$s1->drawCurve(21, -45, 45, -53);
$s1->drawLine(71, -86);
$s1->drawCurve(77, -102, -34, -114);
$s1->drawCurve(-55, -175, -157, 10);
$s1->drawLine(-141, 23);
$s1->drawLine(-144, 12);
$s1->drawCurve(-66, -6, -134, -99);
$s1->drawCurve(-134, -98, -89, -7);
$s1->movePenTo(7475, 2574);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->setRightFill(0x06, 0x04, 0x07);
$s1->drawLine(76, -16);
$s1->drawCurve(82, -14, 35, 92);
$s1->drawLine(-180, 40);
$s1->drawLine(320, 580);
$s1->drawLine(200, -40);
$s1->drawLine(-160, 140);
$s1->drawLine(60, 300);
$s1->drawLine(20, 20);
$s1->drawCurve(80, -27, 98, -68);
$s1->drawCurve(107, -77, 73, -89);
$s1->drawCurve(190, -234, -152, -172);
$s1->drawCurve(-24, -28, -86, 11);
$s1->drawLine(-126, 4);
$s1->drawCurve(-200, -78, 8, -176);
$s1->drawCurve(7, -139, 125, -167);
$s1->drawCurve(-164, -145, -234, -11);
$s1->drawCurve(-219, -11, -203, 107);
$s1->drawCurve(127, 165, 56, 25);
$s1->drawCurve(37, 15, 47, -7);
$s1->movePenTo(8069, 2800);
$s1->drawCurve(62, 82, 101, -5);
$s1->drawCurve(117, -7, 99, -134);
$s1->drawCurve(-55, 15, -23, -20);
$s1->drawCurve(-20, -20, -2, -55);
$s1->drawLine(-160, 160);
$s1->drawLine(-80, -120);
$s1->drawLine(300, -120);
$s1->drawLine(-40, 60);
$s1->drawLine(100, 80);
$s1->drawCurve(91, -115, -70, -84);
$s1->drawCurve(-63, -76, -138, -5);
$s1->drawLine(23, 60);
$s1->drawLine(-15, 15);
$s1->drawCurve(-15, 15, -24, -12);
$s1->drawLine(-29, -38);
$s1->drawCurve(-150, 45, -37, 105);
$s1->drawCurve(-34, 91, 62, 83);
$s1->movePenTo(6728, 2876);
$s1->drawLine(-60, 200);
$s1->drawLine(100, -200);
$s1->drawLine(-40, 0);
$s1->movePenTo(6768, 2597);
$s1->drawCurve(-162, 217, -18, 202);
$s1->drawLine(233, -397);
$s1->drawLine(307, -283);
$s1->drawCurve(-187, 26, -173, 235);
$s1->movePenTo(7268, 3396);
$s1->drawCurve(-87, -292, -133, -88);
$s1->drawCurve(22, 110, 55, 103);
$s1->drawCurve(65, 122, 78, 45);
$s1->movePenTo(7268, 3716);
$s1->drawLine(-260, -20);
$s1->drawCurve(14, 75, 106, 7);
$s1->drawCurve(104, 6, 36, -68);
$s1->movePenTo(7248, 3596);
$s1->setLeftFill(0x06, 0x04, 0x07);
$s1->setRightFill(0xfa, 0xfc, 0xfa);
$s1->drawLine(0, 40);
$s1->drawLine(-240, 0);
$s1->drawLine(0, -40);
$s1->drawLine(240, 0);
$s1->movePenTo(7908, 3736);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->setRightFill(0x06, 0x04, 0x07);
$s1->drawCurve(-377, 104, -523, -4);
$s1->drawCurve(165, 80, 310, -16);
$s1->drawCurve(340, -20, 85, -144);
$s1->movePenTo(7808, 3316);
$s1->drawLine(-820, 180);
$s1->drawCurve(142, 59, 293, -57);
$s1->drawCurve(303, -61, 82, -121);
$s1->movePenTo(8267, 5159);
$s1->drawCurve(-150, -97, -69, -32);
$s1->drawCurve(-255, -119, -45, 265);
$s1->drawLine(360, 200);
$s1->drawLine(-20, -140);
$s1->drawLine(440, 260);
$s1->drawLine(-20, -280);
$s1->drawLine(-40, 60);
$s1->drawCurve(-59, -26, -142, -91);
$s1->movePenTo(8488, 5176);
$s1->drawCurve(-77, -155, -168, -129);
$s1->drawCurve(-158, -121, -177, -55);
$s1->drawCurve(-22, 132, 234, 153);
$s1->drawCurve(178, 115, 190, 60);
$s1->movePenTo(8228, 5636);
$s1->drawLine(240, 120);
$s1->drawLine(-20, -80);
$s1->drawLine(60, -20);
$s1->drawCurve(-39, -43, -73, -27);
$s1->drawCurve(-72, -28, -68, 3);
$s1->drawCurve(-170, 6, 38, 169);
$s1->drawCurve(33, 130, 231, 90);
$s1->drawCurve(-12, -38, 2, -20);
$s1->drawLine(-30, -22);
$s1->drawLine(100, -20);
$s1->drawLine(-18, 71);
$s1->drawLine(-2, 29);
$s1->drawCurve(132, 10, 35, -91);
$s1->drawCurve(32, -86, -59, -113);
$s1->drawLine(-20, 20);
$s1->drawLine(0, 40);
$s1->drawLine(-20, 20);
$s1->drawLine(-40, 0);
$s1->drawLine(0, 100);
$s1->drawLine(-240, -120);
$s1->drawLine(-25, 17);
$s1->drawLine(-35, 3);
$s1->drawLine(36, -89);
$s1->drawLine(4, -31);
$s1->movePenTo(7648, 4836);
$s1->drawCurve(-122, 198, -43, 128);
$s1->drawCurve(-65, 189, 50, 165);
$s1->drawLine(60, -320);
$s1->drawLine(160, -360);
$s1->drawLine(-40, 0);
$s1->movePenTo(7528, 4796);
$s1->drawLine(-80, 200);
$s1->drawLine(120, -200);
$s1->drawLine(-40, 0);
$s1->movePenTo(7408, 4736);
$s1->drawLine(-80, 220);
$s1->drawLine(140, -220);
$s1->drawLine(-60, 0);
$s1->movePenTo(7055, 5518);
$s1->drawCurve(-12, 225, 145, 113);
$s1->drawLine(-97, -380);
$s1->drawLine(37, -340);
$s1->drawLine(-43, 186);
$s1->drawCurve(-27, 115, -3, 81);
$s1->movePenTo(7609, 5363);
$s1->drawCurve(-49, 80, -2, 53);
$s1->drawCurve(-9, 112, 142, 124);
$s1->drawCurve(68, 59, 169, 105);
$s1->drawLine(-40, 79);
$s1->drawCurve(-241, -105, -179, -274);
$s1->drawLine(-220, 240);
$s1->drawCurve(187, 258, 260, 46);
$s1->drawCurve(255, 45, 258, -170);
$s1->drawCurve(-153, -185, -22, -52);
$s1->drawCurve(-58, -138, 133, -124);
$s1->drawLine(-420, -280);
$s1->drawLine(-79, 127);
$s1->movePenTo(6655, 3654);
$s1->drawCurve(-130, -240, -117, -118);
$s1->drawLine(400, 800);
$s1->drawLine(320, -140);
$s1->drawLine(-300, 100);
$s1->drawCurve(-54, -181, -119, -221);
$s1->movePenTo(5368, 776);
$s1->setLeftFill(0x06, 0x04, 0x07);
$s1->setRightFill(0xfa, 0xfc, 0xfa);
$s1->drawLine(20, 100);
$s1->drawLine(40, 0);
$s1->drawLine(20, -100);
$s1->drawLine(-80, 0);
$s1->movePenTo(5648, 876);
$s1->drawLine(20, -100);
$s1->drawLine(-100, 80);
$s1->drawLine(80, 20);
$s1->movePenTo(5488, 776);
$s1->drawLine(0, 300);
$s1->drawLine(100, -60);
$s1->drawCurve(-55, -32, -6, -73);
$s1->drawLine(1, -135);
$s1->drawLine(-40, 0);
$s1->movePenTo(5448, 652);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->setRightFill(0x06, 0x04, 0x07);
$s1->drawCurve(-85, 35, -29, 106);
$s1->drawCurve(-28, 97, 29, 108);
$s1->drawCurve(29, 112, 72, 53);
$s1->drawCurve(79, 61, 103, -42);
$s1->drawCurve(79, -33, 29, -106);
$s1->drawCurve(28, -98, -26, -108);
$s1->drawCurve(-27, -112, -69, -54);
$s1->drawCurve(-79, -61, -105, 42);
$s1->movePenTo(5361, 1221);
$s1->drawCurve(-97, -82, -56, -123);
$s1->drawCurve(-261, 108, -73, 204);
$s1->drawCurve(-47, 127, 1, 341);
$s1->drawLine(280, -60);
$s1->drawCurve(-16, -158, 6, -81);
$s1->drawCurve(8, -143, 102, -38);
$s1->drawLine(40, 400);
$s1->drawLine(580, 0);
$s1->drawLine(0, -400);
$s1->drawLine(120, 20);
$s1->drawLine(0, 420);
$s1->drawLine(280, 60);
$s1->drawCurve(0, -316, -55, -160);
$s1->drawCurve(-85, -246, -240, -78);
$s1->drawCurve(-30, 167, -84, 78);
$s1->drawCurve(-75, 69, -105, -14);
$s1->drawCurve(-100, -13, -93, -82);
$s1->movePenTo(4178, 2602);
$s1->drawCurve(109, -305, -159, -121);
$s1->drawLine(-4, 340);
$s1->drawLine(-196, 480);
$s1->drawCurve(63, -55, 70, -106);
$s1->drawCurve(74, -114, 43, -119);
$s1->movePenTo(3847, 2376);
$s1->drawLine(221, -160);
$s1->drawCurve(-123, -258, -273, -120);
$s1->drawCurve(-290, -126, -235, 144);
$s1->drawLine(65, 107);
$s1->drawCurve(42, 64, 6, 49);
$s1->drawCurve(16, 190, -144, 58);
$s1->drawCurve(-122, 50, -163, -58);
$s1->drawLine(-20, 60);
$s1->drawLine(-80, -20);
$s1->drawCurve(-34, 265, 174, 256);
$s1->drawCurve(161, 236, 259, 123);
$s1->drawCurve(3, -18, 50, -119);
$s1->drawCurve(38, -92, -18, -45);
$s1->drawCurve(-19, -48, -49, -42);
$s1->drawLine(-91, -67);
$s1->drawCurve(-114, -82, 0, -107);
$s1->drawLine(380, 220);
$s1->drawLine(167, -214);
$s1->drawCurve(100, -140, -14, -106);
$s1->drawCurve(-5, -44, -114, -125);
$s1->drawCurve(-105, -116, 14, -29);
$s1->drawCurve(29, -49, 104, 69);
$s1->drawCurve(170, 114, 14, 180);
$s1->movePenTo(3928, 2736);
$s1->drawLine(-101, 200);
$s1->drawLine(141, -200);
$s1->drawLine(-40, 0);
$s1->movePenTo(3767, 2516);
$s1->drawLine(-140, 320);
$s1->drawCurve(68, -44, 47, -98);
$s1->drawCurve(49, -104, -24, -74);
$s1->movePenTo(3827, 2676);
$s1->drawLine(-80, 220);
$s1->drawLine(120, -200);
$s1->drawLine(-40, -20);
$s1->movePenTo(3307, 3296);
$s1->drawCurve(47, 136, 292, 56);
$s1->drawCurve(253, 47, 169, -39);
$s1->drawLine(-321, -37);
$s1->drawLine(-440, -163);
$s1->movePenTo(3964, 3390);
$s1->drawCurve(75, 6, 89, -20);
$s1->drawLine(-281, -60);
$s1->drawCurve(13, 63, 104, 11);
$s1->movePenTo(3867, 3196);
$s1->drawCurve(109, 116, 192, -56);
$s1->drawLine(-301, -60);
$s1->movePenTo(3467, 2916);
$s1->drawCurve(77, 120, 252, 59);
$s1->drawCurve(180, 40, 212, 1);
$s1->drawLine(-361, -87);
$s1->drawLine(-360, -133);
$s1->movePenTo(5868, 2416);
$s1->drawLine(-680, -20);
$s1->drawLine(20, 360);
$s1->drawLine(460, 0);
$s1->drawLine(176, -30);
$s1->drawLine(24, -310);
$s1->movePenTo(6348, 3296);
$s1->drawLine(-60, 240);
$s1->drawCurve(100, -126, -40, -114);
$s1->movePenTo(6328, 3022);
$s1->drawLine(240, -146);
$s1->drawLine(-320, 120);
$s1->drawLine(80, 200);
$s1->drawLine(0, -174);
$s1->movePenTo(2802, 1907);
$s1->setLeftFill(0xff, 0xff, 0xff);
$s1->setRightFill();
$s1->drawLine(-2, -1);
$s1->drawLine(2, 0);
$s1->drawLine(0, 1);
$s1->movePenTo(2894, 1941);
$s1->setRightFill(0x06, 0x04, 0x07);
$s1->drawLine(-118, -51);
$s1->drawLine(33, -6);
$s1->drawLine(13, -1);
$s1->drawLine(21, 4);
$s1->drawLine(3, 1);
$s1->drawLine(4, 1);
$s1->drawLine(0, 1);
$s1->drawLine(6, 3);
$s1->drawLine(12, 11);
$s1->drawLine(26, 37);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawLine(-2, 1);
$s1->drawLine(-1, 1);
$s1->drawLine(-2, 2);
$s1->drawLine(-6, 2);
$s1->drawCurve(-22, 11, -24, -3);
$s1->drawLine(-18, -4);
$s1->drawLine(-15, -6);
$s1->setRightFill();
$s1->drawLine(-5, -3);
$s1->drawLine(-2, -1);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawLine(-5, -4);
$s1->drawLine(-6, -4);
$s1->drawLine(-24, -19);
$s1->drawLine(82, 17);
$s1->setRightFill(0xfa, 0xfc, 0xfa);
$s1->drawLine(4, -4);
$s1->drawLine(12, 5);
$s1->drawLine(-1, 2);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawLine(35, 7);
$s1->movePenTo(2804, 1945);
$s1->setLeftFill();
$s1->drawLine(-5, -3);
$s1->drawLine(-2, -1);
$s1->movePenTo(2605, 1962);
$s1->setLeftFill(0xff, 0xff, 0xff);
$s1->setRightFill(0x06, 0x04, 0x07);
$s1->drawCurve(9, 89, 104, 99);
$s1->drawCurve(106, 98, 111, 22);
$s1->drawCurve(127, 26, 58, -100);
$s1->drawCurve(35, -60, -38, -86);
$s1->drawCurve(-34, -76, -78, -69);
$s1->drawCurve(-78, -68, -76, -22);
$s1->drawCurve(-83, -24, -41, 45);
$s1->drawCurve(-133, 24, 11, 102);
$s1->movePenTo(2844, 1931);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawLine(15, 3);
$s1->movePenTo(2909, 1955);
$s1->setRightFill(0xff, 0xff, 0xff);
$s1->drawLine(9, 2);
$s1->drawLine(-8, -3);
$s1->drawLine(-1, 1);
$s1->movePenTo(2703, 2064);
$s1->setLeftFill(0x06, 0x04, 0x07);
$s1->drawCurve(15, 19, 49, 13);
$s1->drawLine(-80, -100);
$s1->drawCurve(0, 49, 16, 19);
$s1->movePenTo(2707, 1976);
$s1->setLeftFill(0x00, 0x00, 0x00);
$s1->drawLine(60, 120);
$s1->drawCurve(23, -68, -83, -52);
$s1->movePenTo(5046, 5838);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->setRightFill(0x06, 0x04, 0x07);
$s1->drawCurve(-272, 156, -6, 381);
$s1->drawLine(40, 0);
$s1->drawCurve(25, -257, 81, -120);
$s1->drawCurve(147, -234, 413, -25);
$s1->drawCurve(132, -9, 213, 12);
$s1->drawLine(309, 14);
$s1->drawCurve(-87, -36, -141, -5);
$s1->drawLine(-232, 1);
$s1->drawCurve(-413, 0, -209, 122);
$s1->movePenTo(5588, 5636);
$s1->drawLine(500, 20);
$s1->drawLine(20, -520);
$s1->drawLine(-520, -20);
$s1->drawLine(0, 520);
$s1->movePenTo(5028, 5116);
$s1->drawLine(-20, 640);
$s1->drawLine(460, -120);
$s1->drawLine(-20, -520);
$s1->drawLine(-420, 0);
$s1->movePenTo(5908, 5836);
$s1->drawLine(220, 40);
$s1->drawLine(0, -40);
$s1->drawLine(-220, 0);
$s1->movePenTo(3247, 4776);
$s1->drawLine(0, 40);
$s1->drawCurve(292, 7, 74, 215);
$s1->drawCurve(67, 195, -133, 243);
$s1->drawCurve(138, -82, 26, -122);
$s1->drawCurve(24, -109, -67, -117);
$s1->drawCurve(-63, -110, -116, -76);
$s1->drawCurve(-119, -78, -123, -6);
$s1->movePenTo(3807, 4796);
$s1->drawLine(101, 300);
$s1->drawCurve(22, -64, -33, -99);
$s1->drawCurve(-34, -97, -56, -40);
$s1->movePenTo(3852, 4712);
$s1->drawCurve(134, 147, 57, 188);
$s1->drawCurve(59, 198, -44, 191);
$s1->drawCurve(-21, 92, -84, 94);
$s1->drawLine(-146, 154);
$s1->drawCurve(208, -57, 70, -181);
$s1->drawCurve(61, -158, -51, -225);
$s1->drawCurve(-46, -203, -118, -192);
$s1->drawCurve(-116, -187, -128, -84);
$s1->drawCurve(-122, -75, -158, -24);
$s1->drawCurve(-200, -31, -40, 117);
$s1->drawCurve(188, -46, 187, 69);
$s1->drawCurve(175, 64, 135, 149);
$s1->movePenTo(4548, 4996);
$s1->drawLine(-300, -600);
$s1->drawLine(-361, 160);
$s1->drawLine(227, 440);
$s1->drawLine(77, 166);
$s1->drawLine(357, -166);
$s1->movePenTo(3707, 4836);
$s1->drawCurve(-19, 202, 139, 78);
$s1->drawLine(-120, -280);
$s1->movePenTo(6108, 6055);
$s1->drawCurve(-414, -49, -216, 50);
$s1->drawCurve(-356, 81, -14, 338);
$s1->drawLine(57, -143);
$s1->drawCurve(32, -85, 51, -49);
$s1->drawCurve(126, -125, 284, -1);
$s1->drawLine(450, 23);
$s1->drawLine(0, -40);
$s1->movePenTo(6328, 6136);
$s1->drawCurve(-28, -147, -72, -73);
$s1->drawLine(80, 459);
$s1->drawCurve(48, -88, -28, -151);
$s1->movePenTo(5828, 6675);
$s1->drawLine(20, -140);
$s1->drawLine(-680, -20);
$s1->drawCurve(-1, 86, -22, 47);
$s1->drawCurve(-20, 41, -25, -5);
$s1->drawCurve(-25, -6, -10, -48);
$s1->drawCurve(-12, -54, 15, -81);
$s1->drawLine(-240, -60);
$s1->drawCurve(-1, 271, 58, 140);
$s1->drawCurve(81, 199, 222, 50);
$s1->drawCurve(20, -114, 15, -53);
$s1->drawCurve(25, -88, 63, -55);
$s1->drawCurve(166, -153, 161, 134);
$s1->drawCurve(66, 56, 42, 91);
$s1->drawCurve(41, 90, 1, 92);
$s1->drawCurve(244, -24, 110, -209);
$s1->drawCurve(41, -79, 33, -125);
$s1->drawLine(52, -223);
$s1->drawCurve(-171, 27, -56, 27);
$s1->drawCurve(-94, 43, 1, 143);
$s1->drawLine(-120, 0);
$s1->movePenTo(6128, 5975);
$s1->drawLine(-220, -39);
$s1->drawLine(0, 39);
$s1->drawLine(220, 0);
$s1->movePenTo(5330, 6929);
$s1->drawCurve(-43, 89, 11, 116);
$s1->drawCurve(9, 117, 59, 73);
$s1->drawCurve(64, 81, 98, -14);
$s1->drawCurve(97, -14, 48, -100);
$s1->drawCurve(43, -91, -12, -118);
$s1->drawCurve(-12, -119, -60, -72);
$s1->drawCurve(-66, -81, -98, 19);
$s1->drawCurve(-92, 15, -46, 99);
$s1->movePenTo(5448, 7255);
$s1->setLeftFill(0x06, 0x04, 0x07);
$s1->setRightFill(0xfa, 0xfc, 0xfa);
$s1->drawLine(0, -80);
$s1->drawLine(-80, 80);
$s1->drawLine(80, 0);
$s1->movePenTo(5548, 7255);
$s1->drawLine(0, -320);
$s1->drawLine(-120, 0);
$s1->drawLine(60, 320);
$s1->drawLine(60, 0);
$s1->movePenTo(5588, 7255);
$s1->drawLine(80, -100);
$s1->drawCurve(-53, 10, -15, 20);
$s1->drawCurve(-15, 19, 3, 51);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(6048, 1896);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->drawLine(-601, -7);
$s1->drawLine(-260, -133);
$s1->drawCurve(129, 185, 264, 16);
$s1->drawLine(468, -21);
$s1->drawLine(0, -40);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(5531, 2332);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->drawCurve(-300, -2, -184, -144);
$s1->drawCurve(-66, -49, -80, -129);
$s1->drawCurve(-82, -131, -52, -41);
$s1->drawCurve(26, 412, 472, 81);
$s1->drawCurve(162, 28, 249, -6);
$s1->drawLine(372, -15);
$s1->drawLine(0, -40);
$s1->drawLine(-517, 36);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(6268, 1856);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->drawLine(-100, 320);
$s1->drawCurve(153, -175, -53, -145);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(5828, 2176);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->drawLine(0, 40);
$s1->drawLine(220, -20);
$s1->drawLine(-220, -20);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(7107, 2908);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->drawCurve(162, -165, 179, -47);
$s1->drawCurve(-53, -49, -68, 20);
$s1->drawCurve(-44, 13, -75, 56);
$s1->drawCurve(-376, 251, 16, 229);
$s1->drawLine(259, -308);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(7241, 3614);
$s1->setRightFill(0x63, 0x62, 0x63);
$s1->drawLine(7, 22);
$s1->drawLine(-16, 0);
$s1->drawCurve(-15, 14, -35, 4);
$s1->drawCurve(-75, 9, -59, -27);
$s1->drawLine(184, 0);
$s1->drawCurve(8, -9, 1, -13);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(3147, 4535);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->drawCurve(-81, 15, -120, 58);
$s1->drawCurve(-132, 64, -98, 82);
$s1->drawCurve(-262, 217, 143, 180);
$s1->drawCurve(34, 44, 64, 9);
$s1->drawLine(120, 2);
$s1->drawCurve(157, -1, 51, 133);
$s1->drawCurve(41, 97, -91, 116);
$s1->drawLine(-66, 89);
$s1->drawCurve(-30, 51, 6, 47);
$s1->drawCurve(12, 96, 172, 61);
$s1->drawCurve(151, 54, 187, -49);
$s1->drawCurve(81, -22, 241, -103);
$s1->drawLine(-160, -240);
$s1->drawLine(-200, 117);
$s1->drawCurve(-126, 47, -34, -124);
$s1->drawCurve(298, -29, -77, -246);
$s1->drawCurve(-22, -71, -79, -153);
$s1->drawCurve(-79, -154, -21, -67);
$s1->drawLine(-360, 140);
$s1->drawCurve(-5, -101, 114, -64);
$s1->drawCurve(67, -38, 144, -37);
$s1->drawLine(-40, -220);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(2787, 5288);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->drawLine(-188, 11);
$s1->drawCurve(-116, 13, -50, 53);
$s1->drawCurve(-98, 100, 56, 120);
$s1->drawCurve(56, 120, 140, -10);
$s1->drawCurve(90, -8, 89, -49);
$s1->drawCurve(100, -53, 37, -74);
$s1->drawCurve(43, -81, -33, -73);
$s1->drawCurve(-35, -76, -91, 7);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(2447, 5395);
$s1->setLeftFill(0x06, 0x04, 0x07);
$s1->drawLine(40, 80);
$s1->drawLine(60, -100);
$s1->drawLine(-100, 20);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(5768, 6155);
$s1->setLeftFill(0xfa, 0xfc, 0xfa);
$s1->drawLine(100, 320);
$s1->drawCurve(87, -58, -43, -124);
$s1->drawCurve(-43, -122, -101, -16);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(2522, 5549);
$s1->setLeftFill(0x06, 0x04, 0x07);
$s1->drawLine(40, 80);
$s1->drawLine(60, -100);
$s1->drawLine(-100, 20);
$i1 = $conference1->add($s1);
$i1->scaleTo(0.5);
$i1->moveTo(-2720, -1890);
$conference1->nextFrame();  # end of frame 1


$conference2 = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(6139, 1671);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawCurve(28, -75, 62, -60);
$s1->drawCurve(112, -109, 158, 0);
$s1->drawCurve(158, 0, 112, 109);
$s1->drawCurve(62, 60, 28, 75);
$s1->setRightFill(0x99, 0x99, 0x99, 0x00);
$s1->drawLine(504, 0);
$s1->drawCurve(33, 0, 23, 23);
$s1->drawCurve(24, 24, 0, 33);
$s1->drawLine(0, 3987);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawLine(0, 823);
$s1->drawLine(0, 12);
$s1->drawLine(-305, 0);
$s1->drawLine(0, -123);
$s1->setRightFill(0x99, 0x99, 0x99, 0x00);
$s1->drawLine(-122, 0);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawLine(0, 123);
$s1->drawLine(-928, 0);
$s1->drawLine(0, -123);
$s1->setRightFill(0x99, 0x99, 0x99, 0x00);
$s1->drawLine(-122, 0);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawLine(0, 123);
$s1->drawLine(-354, 0);
$s1->drawLine(0, -12);
$s1->drawLine(0, -111);
$s1->setRightFill(0x99, 0x99, 0x99, 0x00);
$s1->drawLine(-217, 0);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawLine(0, 111);
$s1->drawLine(0, 12);
$s1->drawLine(-305, 0);
$s1->drawLine(0, -123);
$s1->setRightFill(0x99, 0x99, 0x99, 0x00);
$s1->drawLine(-122, 0);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawLine(0, 123);
$s1->drawLine(-928, 0);
$s1->drawLine(0, -123);
$s1->setRightFill(0x99, 0x99, 0x99, 0x00);
$s1->drawLine(-122, 0);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawLine(0, 123);
$s1->drawLine(-354, 0);
$s1->drawLine(0, -12);
$s1->drawLine(0, -813);
$s1->drawCurve(0, -166, 117, -117);
$s1->drawLine(2, -2);
$s1->setRightFill(0x99, 0x99, 0x99, 0x00);
$s1->drawLine(0, -864);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawCurve(-62, 43, -57, 44);
$s1->drawCurve(-9, -41, 0, -45);
$s1->drawLine(0, -1238);
$s1->drawCurve(0, -166, 117, -117);
$s1->drawLine(11, -10);
$s1->setRightFill(0x99, 0x99, 0x99, 0x00);
$s1->drawLine(0, -1318);
$s1->drawCurve(0, -33, 23, -24);
$s1->drawCurve(24, -23, 33, 0);
$s1->drawLine(2376, 0);
$s1->setLeftFill(0x00, 0x00, 0x00);
$s1->drawCurve(-22, 60, 0, 69);
$s1->drawCurve(0, 155, 112, 109);
$s1->drawCurve(112, 109, 158, 0);
$s1->drawCurve(158, 0, 112, -109);
$s1->drawCurve(112, -109, 0, -155);
$s1->drawCurve(0, -69, -22, -60);
$s1->movePenTo(7441, 2773);
$s1->setLeftFill(0x99, 0x99, 0x99, 0x00);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawLine(0, 730);
$s1->drawLine(-398, 25);
$s1->drawLine(0, -506);
$s1->drawCurve(0, -25, -18, -18);
$s1->drawCurve(-17, -17, -25, 0);
$s1->drawLine(-1, 0);
$s1->drawCurve(-25, 0, -18, 17);
$s1->drawCurve(-17, 18, 0, 25);
$s1->drawLine(0, 517);
$s1->drawLine(-429, 53);
$s1->drawLine(-385, 64);
$s1->drawLine(0, -634);
$s1->drawCurve(0, -25, -18, -18);
$s1->drawCurve(-17, -17, -25, 0);
$s1->drawLine(-1, 0);
$s1->drawCurve(-25, 0, -18, 17);
$s1->drawCurve(-17, 18, 0, 25);
$s1->drawLine(0, 658);
$s1->drawCurve(-193, 38, -184, 47);
$s1->drawLine(0, -992);
$s1->drawCurve(0, -166, 117, -117);
$s1->drawCurve(117, -117, 166, 0);
$s1->drawLine(1031, 0);
$s1->drawCurve(166, 0, 117, 117);
$s1->drawCurve(117, 117, 0, 166);
$s1->movePenTo(6796, 4533);
$s1->drawCurve(112, 109, 0, 155);
$s1->drawCurve(0, 155, -112, 109);
$s1->drawCurve(-112, 109, -158, 0);
$s1->drawCurve(-158, 0, -112, -109);
$s1->drawCurve(-112, -109, 0, -155);
$s1->drawCurve(0, -155, 112, -109);
$s1->drawCurve(112, -109, 158, 0);
$s1->drawCurve(158, 0, 112, 109);
$s1->movePenTo(7138, 6450);
$s1->drawLine(0, -532);
$s1->drawCurve(0, -25, -18, -18);
$s1->drawCurve(-18, -18, -25, 0);
$s1->drawCurve(-25, 0, -18, 18);
$s1->drawCurve(-18, 18, 0, 25);
$s1->drawLine(0, 532);
$s1->movePenTo(5612, 6450);
$s1->drawLine(0, -702);
$s1->drawCurve(0, -166, 117, -117);
$s1->drawCurve(117, -117, 166, 0);
$s1->drawLine(1031, 0);
$s1->drawCurve(166, 0, 117, 117);
$s1->drawCurve(113, 113, 4, 160);
$s1->movePenTo(4511, 2774);
$s1->drawCurve(-158, 0, -112, -109);
$s1->drawCurve(-112, -109, 0, -155);
$s1->drawCurve(0, -155, 112, -109);
$s1->drawCurve(112, -109, 158, 0);
$s1->drawCurve(158, 0, 112, 109);
$s1->drawCurve(112, 109, 0, 155);
$s1->drawCurve(0, 155, -112, 109);
$s1->drawCurve(-112, 109, -158, 0);
$s1->movePenTo(3683, 4599);
$s1->setLeftFill(0x00, 0x00, 0x00);
$s1->setRightFill(0x99, 0x99, 0x99, 0x00);
$s1->drawCurve(62, -43, 66, -42);
$s1->drawLine(142, -88);
$s1->drawLine(0, -839);
$s1->drawCurve(0, -25, 17, -18);
$s1->drawCurve(18, -17, 25, 0);
$s1->drawLine(1, 0);
$s1->drawCurve(25, 0, 17, 17);
$s1->drawCurve(18, 18, 0, 25);
$s1->drawLine(0, 769);
$s1->drawCurve(365, -204, 421, -156);
$s1->drawLine(0, -409);
$s1->drawCurve(0, -25, 17, -18);
$s1->drawCurve(18, -17, 25, 0);
$s1->drawLine(1, 0);
$s1->drawCurve(25, 0, 17, 17);
$s1->drawCurve(18, 18, 0, 25);
$s1->drawLine(0, 365);
$s1->drawCurve(197, -69, 208, -59);
$s1->drawLine(0, -462);
$s1->drawCurve(0, -166, -117, -117);
$s1->drawCurve(-117, -117, -166, 0);
$s1->drawLine(-1031, 0);
$s1->drawCurve(-158, 0, -114, 107);
$s1->movePenTo(3683, 5463);
$s1->setLeftFill(0x99, 0x99, 0x99, 0x00);
$s1->setRightFill(0x00, 0x00, 0x00);
$s1->drawCurve(116, -115, 165, 0);
$s1->drawLine(1031, 0);
$s1->drawCurve(166, 0, 117, 117);
$s1->drawCurve(117, 117, 0, 166);
$s1->drawLine(0, 702);
$s1->movePenTo(5090, 6450);
$s1->drawLine(0, -532);
$s1->drawCurve(0, -25, -18, -18);
$s1->drawCurve(-18, -18, -25, 0);
$s1->drawCurve(-25, 0, -18, 18);
$s1->drawCurve(-18, 18, 0, 25);
$s1->drawLine(0, 532);
$s1->movePenTo(6088, 6450);
$s1->drawLine(0, -532);
$s1->drawCurve(0, -25, -18, -18);
$s1->drawCurve(-18, -18, -25, 0);
$s1->drawCurve(-25, 0, -18, 18);
$s1->drawCurve(-18, 18, 0, 25);
$s1->drawLine(0, 532);
$s1->movePenTo(4784, 4530);
$s1->drawCurve(112, 109, 0, 155);
$s1->drawCurve(0, 155, -112, 109);
$s1->drawCurve(-112, 109, -158, 0);
$s1->drawCurve(-158, 0, -112, -109);
$s1->drawCurve(-134, -130, 0, -133);
$s1->drawCurve(0, -135, 134, -130);
$s1->drawCurve(112, -109, 158, 0);
$s1->drawCurve(158, 0, 112, 109);
$s1->movePenTo(4040, 6450);
$s1->drawLine(0, -532);
$s1->drawCurve(0, -25, -18, -18);
$s1->drawCurve(-18, -18, -25, 0);
$s1->drawCurve(-25, 0, -18, 18);
$s1->drawCurve(-18, 18, 0, 25);
$s1->drawLine(0, 532);
$i1 = $conference2->add($s1);
$i1->scaleTo(0.5);
$i1->moveTo(-2720, -1890);
$conference2->nextFrame();  # end of frame 1



# status log toolbar
#  $statusbar = new SWF::Sprite();
### Shape 1 ###
#  $s1 = new SWF::Shape();
#  $s1->movePenTo(19920, 0);
#  $s1->setRightFill(0xcc, 0xcc, 0xcc);
#  $s1->drawLine(0, 600);
#  $s1->setLine(20, 0x00, 0x00, 0x00);
#  $s1->drawLine(-19920, 0);
#  $s1->setLine(20, 0xcc, 0xcc, 0xcc);
#  $s1->drawLine(0, -600);
#  $s1->setLine(20, 0x99, 0x99, 0x99);
#  $s1->drawLine(19920, 0);
#  $i1 = $statusbar->add($s1);
#  $i1->scaleTo(0.5);
#  $statusbar->nextFrame();  # end of frame 1



$extrainfo = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(5400, 0);
$s1->setRightFill(0xcc, 0xcc, 0xcc);
$s1->setLine(20, 0xcc, 0xcc, 0xcc);
$s1->drawLine(0, 600);
$s1->setLine(20, 0x99, 0x99, 0x99);
$s1->drawLine(-5400, 0);
$s1->setLine(20, 0xcc, 0xcc, 0xcc);
$s1->drawLine(0, -600);
$s1->setLine(20, 0x99, 0x99, 0x99);
$s1->drawLine(5400, 0);
$s1->setLeftFill();
$s1->setRightFill();
$s1->movePenTo(5367, 91);
$s1->setRightFill(0xcc, 0xcc, 0xcc);
$s1->setLine(20, 0xff, 0xff, 0xff);
$s1->drawLine(0, 400);
$s1->drawLine(-3900, 0);
$s1->setLine(20, 0x66, 0x66, 0x66);
$s1->drawLine(0, -400);
$s1->setLine(20, 0x99, 0x99, 0x99);
$s1->drawLine(3900, 0);
$i2 = $extrainfo->add($s1);
$i2->scaleTo(0.5);

#$s3 = new SWF::TextField(SWFTEXTFIELD_USEFONT );
#$s3->setBounds(3740, 398);
#$s3->setFont($font_general);
#$s3->setHeight(320);
###$s3->setColor(0x00, 0x00, 0x00, 0xff);
#$s3->align(SWFTEXTFIELD_ALIGN_LEFT);
#$s3->setName('clidvalue');
#$i3 = $extrainfo->add($s3);
#$i3->scaleTo(0.5, 0.5);
#$i3->moveTo(770, 65);
#$i3->setName('clid_text');

$extrainfo->nextFrame();  



$boton_ayuda = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(439, 222);
$s1->setLeftFill(0xff, 0xa8, 0x37);
$s1->setRightFill(0x79, 0x79, 0x79);
$s1->drawCurve(-1, 24, -16, 23);
$s1->drawLine(-34, 35);
$s1->drawCurve(-32, 29, 2, 32);
$s1->drawLine(0, 9);
$s1->drawLine(-78, 0);
$s1->drawLine(-2, -13);
$s1->drawCurve(-2, -41, 37, -35);
$s1->drawLine(24, -27);
$s1->drawLine(10, -25);
$s1->drawCurve(0, -28, -42, -1);
$s1->drawCurve(-34, 0, -25, 15);
$s1->drawLine(-20, -53);
$s1->drawCurve(42, -21, 57, 1);
$s1->drawCurve(55, 0, 31, 23);
$s1->drawCurve(27, 20, 1, 33);
$s1->movePenTo(418, 82);
$s1->setLeftFill(0x79, 0x79, 0x79);
$s1->setRightFill(0xcc, 0xcc, 0xcc);
$s1->drawCurve(-46, -21, -49, -1);
$s1->drawCurve(-51, -1, -46, 19);
$s1->drawCurve(-44, 18, -35, 34);
$s1->drawCurve(-35, 33, -20, 44);
$s1->drawCurve(-21, 46, -2, 49);
$s1->drawCurve(-3, 50, 19, 46);
$s1->drawCurve(17, 45, 33, 36);
$s1->drawCurve(33, 35, 44, 21);
$s1->drawCurve(45, 22, 49, 3);
$s1->drawCurve(50, 2, 47, -17);
$s1->drawCurve(45, -16, 36, -33);
$s1->drawCurve(36, -33, 22, -43);
$s1->drawCurve(22, -45, 4, -49);
$s1->drawLine(0, -15);
$s1->drawLine(0, -2);
$s1->drawCurve(0, -50, -19, -45);
$s1->drawCurve(-19, -44, -34, -34);
$s1->drawCurve(-34, -35, -44, -19);
$s1->movePenTo(518, 404);
$s1->setRightFill(0xff, 0xa8, 0x37);
$s1->drawCurve(-21, 44, -38, 32);
$s1->drawCurve(-37, 32, -50, 12);
$s1->drawLine(-98, 3);
$s1->drawCurve(-47, -10, -39, -29);
$s1->drawCurve(-39, -29, -24, -43);
$s1->drawCurve(-25, -43, -2, -51);
$s1->drawCurve(-2, -50, 19, -47);
$s1->drawCurve(28, -72, 72, -37);
$s1->drawCurve(71, -38, 77, 16);
$s1->drawCurve(76, 15, 50, 62);
$s1->drawCurve(49, 61, 1, 78);
$s1->drawLine(0, 1);
$s1->drawCurve(-1, 49, -20, 44);
$s1->movePenTo(278, 410);
$s1->setLeftFill(0xff, 0xa8, 0x37);
$s1->setRightFill(0x79, 0x79, 0x79);
$s1->drawCurve(15, -12, 23, 0);
$s1->drawCurve(24, 0, 15, 12);
$s1->drawCurve(14, 12, 0, 20);
$s1->drawCurve(0, 20, -14, 12);
$s1->drawCurve(-15, 13, -23, 0);
$s1->drawLine(-1, 0);
$s1->drawCurve(-23, 0, -15, -13);
$s1->drawCurve(-15, -13, 0, -19);
$s1->drawCurve(0, -20, 15, -12);
$s1->movePenTo(640, 600);
$s1->setLeftFill();
$s1->setRightFill(0xcc, 0xcc, 0xcc);
$s1->setLine(20, 0x99, 0x99, 0x99); # raya inferior
$s1->drawLine(-640, 0);
$s1->setLine(20, 0xcc, 0xcc, 0xcc);
$s1->drawLine(0, -600);
$s1->setLine(20, 0x99, 0x99, 0x99);
$s1->drawLine(640, 0);
$s1->setLine(20, 0xcc, 0xcc, 0xcc);
$s1->drawLine(0, 600);

### Shape 2 ###
$s2 = new SWF::Shape();
$s2->movePenTo(439, 221);
$s2->setLeftFill(0x79, 0x79, 0x79);
$s2->setRightFill(0xff, 0xa8, 0x37);
$s2->drawCurve(-1, 25, -16, 23);
$s2->drawLine(-34, 35);
$s2->drawCurve(-32, 28, 1, 32);
$s2->drawLine(0, 9);
$s2->drawLine(-78, 0);
$s2->drawLine(-1, -13);
$s2->drawCurve(-2, -40, 37, -36);
$s2->drawLine(24, -26);
$s2->drawLine(10, -25);
$s2->drawCurve(0, -29, -42, 0);
$s2->drawCurve(-34, 0, -25, 14);
$s2->drawLine(-20, -53);
$s2->drawCurve(42, -20, 57, 0);
$s2->drawCurve(55, 0, 31, 23);
$s2->drawCurve(27, 21, 1, 32);
$s2->movePenTo(515, 157);
$s2->setLeftFill(0xcc, 0xcc, 0xcc);
$s2->drawLine(25, 40);
$s2->drawLine(9, 17);
$s2->drawLine(8, 23);
$s2->drawLine(11, 72);
$s2->drawLine(0, 2);
$s2->drawLine(0, 15);
$s2->drawLine(-16, 71);
$s2->drawLine(-10, 23);
$s2->drawCurve(-22, 43, -36, 33);
$s2->drawCurve(-36, 33, -45, 16);
$s2->drawCurve(-47, 17, -50, -2);
$s2->drawCurve(-49, -3, -45, -22);
$s2->drawCurve(-44, -21, -33, -35);
$s2->drawLine(-29, -39);
$s2->drawLine(-21, -42);
$s2->drawLine(-1, -1);
$s2->drawLine(-13, -46);
$s2->setRightFill();
$s2->drawLine(-3, -50);
$s2->drawCurve(2, -49, 22, -45);
$s2->drawCurve(20, -44, 35, -34);
$s2->drawLine(18, -15);
$s2->setRightFill(0xff, 0xa8, 0x37);
$s2->drawLine(61, -36);
$s2->drawCurve(46, -19, 50, 1);
$s2->drawLine(1, 0);
$s2->drawLine(4, 0);
$s2->drawLine(69, 13);
$s2->setRightFill();
$s2->drawLine(22, 8);
$s2->drawCurve(44, 20, 34, 34);
$s2->drawLine(19, 22);
$s2->setLeftFill(0xff, 0xa8, 0x37);
$s2->drawLine(-19, -21);
$s2->drawCurve(-34, -35, -44, -19);
$s2->drawLine(-22, -9);
$s2->movePenTo(539, 311);
$s2->setRightFill(0x79, 0x79, 0x79);
$s2->drawCurve(-1, 49, -20, 44);
$s2->drawCurve(-21, 44, -38, 32);
$s2->drawCurve(-37, 32, -50, 12);
$s2->drawLine(-98, 3);
$s2->drawCurve(-47, -10, -39, -29);
$s2->drawCurve(-39, -29, -24, -43);
$s2->drawCurve(-25, -43, -2, -51);
$s2->drawCurve(-2, -50, 19, -47);
$s2->drawCurve(28, -72, 72, -37);
$s2->drawCurve(71, -38, 77, 16);
$s2->drawCurve(76, 15, 50, 62);
$s2->drawCurve(49, 61, 1, 78);
$s2->drawLine(0, 1);
$s2->movePenTo(165, 114);
$s2->setRightFill();
$s2->drawCurve(-10, 7, -8, 9);
$s2->drawCurve(-35, 33, -20, 44);
$s2->drawCurve(-21, 46, -2, 49);
$s2->drawLine(2, 49);
$s2->movePenTo(278, 409);
$s2->setLeftFill(0x79, 0x79, 0x79);
$s2->setRightFill(0xff, 0xa8, 0x37);
$s2->drawCurve(15, -12, 23, 0);
$s2->drawCurve(24, 0, 14, 12);
$s2->drawCurve(15, 12, 0, 20);
$s2->drawCurve(0, 19, -14, 13);
$s2->drawCurve(-15, 12, -23, 1);
$s2->drawLine(-1, 0);
$s2->drawCurve(-24, -1, -14, -12);
$s2->drawCurve(-15, -13, 0, -19);
$s2->drawCurve(0, -20, 15, -12);
$s2->movePenTo(640, 600);
$s2->setLeftFill();
$s2->setRightFill(0xcc, 0xcc, 0xcc);
$s2->setLine(20, 0x99, 0x99, 0x99); # raya inferior
$s2->drawLine(-640, 0);
$s2->setLine(20, 0xcc, 0xcc, 0xcc);
$s2->drawLine(0, -600);
$s2->setLine(20, 0x99, 0x99, 0x99);
$s2->drawLine(640, 0);
$s2->setLine(20, 0xcc, 0xcc, 0xcc);
$s2->drawLine(0, 600);

$i1 = $boton_ayuda->add($s1);
$i1->scaleTo(0.5);
$boton_ayuda->nextFrame();  # end of frame 1
$boton_ayuda->remove($i1);
$i1 = $boton_ayuda->add($s2);
$i1->scaleTo(0.5);
$boton_ayuda->nextFrame();  # end of frame 1


$boton_debug = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(415, 159);
$s1->setLeftFill(0x79, 0x79, 0x79);
$s1->setRightFill(0xff, 0xa8, 0x37);
$s1->drawCurve(-10, 0, 0, 10);
$s1->drawCurve(3, 58, -32, 23);
$s1->drawLine(-23, 11);
$s1->drawLine(-9, 1);
$s1->drawLine(-4, 1);
$s1->drawLine(-17, -2);
$s1->drawLine(-21, 4);
$s1->drawCurve(-3, -3, -4, 0);
$s1->drawCurve(-14, -1, -16, -10);
$s1->drawCurve(-34, -22, 3, -60);
$s1->drawCurve(0, -10, -10, 0);
$s1->drawCurve(-10, 0, 0, 10);
$s1->drawCurve(-3, 66, 39, 30);
$s1->drawLine(27, 15);
$s1->drawLine(-50, 3);
$s1->drawCurve(-26, -8, -21, -22);
$s1->drawLine(-15, 1);
$s1->drawCurve(-7, 7, 7, 7);
$s1->drawCurve(26, 26, 33, 10);
$s1->drawLine(33, 3);
$s1->drawLine(-6, 18);
$s1->drawLine(-2, 1);
$s1->drawCurve(-34, 25, -29, -17);
$s1->drawCurve(-8, -5, -5, 9);
$s1->drawCurve(-6, 9, 9, 5);
$s1->drawCurve(34, 20, 38, -21);
$s1->drawCurve(1, 33, 21, 24);
$s1->drawCurve(22, 25, 31, 0);
$s1->drawCurve(30, 0, 22, -25);
$s1->drawCurve(20, -23, 2, -31);
$s1->drawLine(66, -2);
$s1->drawCurve(9, -5, -5, -9);
$s1->drawCurve(-6, -9, -8, 5);
$s1->drawCurve(-26, 16, -31, -20);
$s1->drawLine(-7, -23);
$s1->drawLine(26, -3);
$s1->drawCurve(33, -10, 26, -26);
$s1->drawCurve(7, -7, -7, -7);
$s1->drawLine(-14, -1);
$s1->drawCurve(-22, 22, -25, 8);
$s1->drawLine(-40, 1);
$s1->drawLine(-7, -6);
$s1->drawLine(22, -12);
$s1->drawCurve(40, -29, -2, -68);
$s1->drawCurve(-1, -10, -10, 0);
$s1->movePenTo(416, 81);
$s1->setRightFill(0xcc, 0xcc, 0xcc);
$s1->drawCurve(-46, -20, -49, -1);
$s1->drawCurve(-50, -1, -46, 19);
$s1->drawCurve(-45, 17, -34, 34);
$s1->drawCurve(-36, 34, -20, 44);
$s1->drawCurve(-21, 45, -2, 49);
$s1->drawCurve(-2, 50, 18, 46);
$s1->drawCurve(17, 45, 34, 36);
$s1->drawCurve(33, 35, 44, 21);
$s1->drawCurve(44, 21, 50, 3);
$s1->drawCurve(49, 3, 47, -17);
$s1->drawCurve(45, -16, 36, -33);
$s1->drawCurve(36, -33, 22, -43);
$s1->drawCurve(22, -44, 4, -49);
$s1->drawLine(0, -16);
$s1->drawLine(0, -1);
$s1->drawCurve(-1, -50, -19, -45);
$s1->drawCurve(-19, -45, -33, -34);
$s1->drawCurve(-34, -34, -44, -20);
$s1->movePenTo(537, 310);
$s1->setRightFill(0xff, 0xa8, 0x37);
$s1->drawCurve(-1, 49, -20, 44);
$s1->drawCurve(-21, 44, -38, 32);
$s1->drawCurve(-37, 32, -50, 12);
$s1->drawCurve(-49, 12, -48, -10);
$s1->drawCurve(-48, -9, -39, -29);
$s1->drawCurve(-39, -29, -24, -43);
$s1->drawCurve(-24, -43, -2, -51);
$s1->drawCurve(-3, -50, 19, -46);
$s1->drawCurve(29, -73, 72, -37);
$s1->drawCurve(70, -37, 77, 15);
$s1->drawCurve(76, 15, 50, 62);
$s1->drawCurve(49, 61, 1, 78);
$s1->drawLine(0, 1);
$s1->movePenTo(277, 206);
$s1->setLeftFill(0xff, 0xa8, 0x37);
$s1->setRightFill(0x79, 0x79, 0x79);
$s1->drawCurve(0, -19, 13, -12);
$s1->drawCurve(13, -13, 18, 0);
$s1->drawCurve(18, 0, 13, 13);
$s1->drawCurve(13, 12, 0, 19);
$s1->drawCurve(0, 18, -13, 13);
$s1->drawCurve(-13, 12, -18, 1);
$s1->drawCurve(-18, -1, -13, -12);
$s1->drawCurve(-13, -13, 0, -18);
$s1->movePenTo(640, 600);
$s1->setLeftFill();
$s1->setRightFill(0xcc, 0xcc, 0xcc);
$s1->setLine(20, 0x99, 0x99, 0x99); # raya inferior
$s1->drawLine(-640, 0);
$s1->setLine(20, 0xcc, 0xcc, 0xcc);
$s1->drawLine(0, -600);
$s1->setLine(20, 0x99, 0x99, 0x99);
$s1->drawLine(640, 0);
$s1->setLine(20, 0xcc, 0xcc, 0xcc);
$s1->drawLine(0, 600);

### Shape 2 ###
$s2 = new SWF::Shape();
$s2->movePenTo(366, 554);
$s2->setLeftFill(0xff, 0xa8, 0x37);
$s2->drawLine(35, -10);
$s2->drawCurve(45, -16, 36, -33);
$s2->drawCurve(36, -33, 22, -43);
$s2->drawCurve(22, -44, 4, -49);
$s2->drawLine(0, -16);
$s2->drawLine(0, -1);
$s2->drawCurve(-1, -50, -19, -45);
$s2->drawCurve(-19, -45, -33, -34);
$s2->drawCurve(-34, -34, -44, -20);
$s2->drawCurve(-46, -20, -49, -1);
$s2->drawLine(-39, 2);
$s2->setRightFill(0xcc, 0xcc, 0xcc);
$s2->drawLine(-38, 9);
$s2->drawLine(-18, 6);
$s2->drawLine(-1, 1);
$s2->drawLine(-42, 21);
$s2->drawLine(-37, 30);
$s2->drawCurve(-36, 34, -20, 44);
$s2->drawCurve(-21, 45, -2, 49);
$s2->drawCurve(-2, 50, 18, 46);
$s2->drawCurve(17, 45, 34, 36);
$s2->drawCurve(33, 35, 44, 21);
$s2->drawLine(46, 17);
$s2->drawLine(48, 7);
$s2->drawLine(1, 0);
$s2->drawLine(60, -4);
$s2->setLeftFill();
$s2->drawLine(37, -10);
$s2->drawCurve(44, -17, 37, -32);
$s2->drawCurve(36, -33, 22, -43);
$s2->drawCurve(22, -44, 4, -49);
$s2->drawLine(0, -16);
$s2->drawLine(0, -1);
$s2->drawCurve(0, -50, -19, -45);
$s2->drawCurve(-19, -45, -34, -34);
$s2->drawCurve(-34, -34, -44, -20);
$s2->drawCurve(-46, -20, -49, -1);
$s2->drawLine(-41, 2);
$s2->movePenTo(407, 169);
$s2->setLeftFill(0xff, 0xa8, 0x37);
$s2->setRightFill(0x79, 0x79, 0x79);
$s2->drawCurve(3, 58, -32, 23);
$s2->drawLine(-23, 11);
$s2->drawLine(-9, 1);
$s2->drawLine(-4, 1);
$s2->drawLine(-18, -2);
$s2->drawLine(-21, 3);
$s2->drawLine(-6, -2);
$s2->drawLine(-30, -11);
$s2->drawCurve(-34, -22, 3, -60);
$s2->drawCurve(0, -10, -10, 0);
$s2->drawCurve(-11, 0, 0, 10);
$s2->drawCurve(-3, 66, 39, 30);
$s2->drawLine(27, 15);
$s2->drawLine(-50, 3);
$s2->drawCurve(-26, -8, -21, -22);
$s2->drawCurve(-7, -7, -7, 7);
$s2->drawCurve(-8, 7, 8, 8);
$s2->drawCurve(26, 26, 33, 10);
$s2->drawLine(32, 3);
$s2->drawLine(-6, 18);
$s2->drawLine(-1, 1);
$s2->drawCurve(-35, 25, -28, -17);
$s2->drawCurve(-9, -5, -5, 9);
$s2->drawCurve(-6, 8, 9, 6);
$s2->drawCurve(34, 20, 39, -21);
$s2->drawCurve(1, 33, 21, 24);
$s2->drawCurve(21, 25, 31, 0);
$s2->drawCurve(31, 0, 22, -25);
$s2->drawCurve(19, -23, 3, -31);
$s2->drawLine(66, -2);
$s2->drawCurve(9, -6, -5, -8);
$s2->drawCurve(-6, -9, -8, 5);
$s2->drawCurve(-27, 16, -31, -20);
$s2->drawLine(-7, -23);
$s2->drawLine(27, -3);
$s2->drawCurve(33, -10, 26, -26);
$s2->drawCurve(7, -8, -7, -7);
$s2->drawCurve(-8, -7, -6, 7);
$s2->drawCurve(-22, 22, -26, 8);
$s2->drawLine(-39, 1);
$s2->drawLine(-6, -6);
$s2->drawLine(21, -12);
$s2->drawCurve(40, -29, -2, -68);
$s2->drawCurve(-1, -10, -10, 0);
$s2->drawCurve(-11, 0, 1, 10);
$s2->movePenTo(537, 310);
$s2->drawCurve(-1, 49, -20, 44);
$s2->drawCurve(-21, 44, -38, 32);
$s2->drawCurve(-37, 32, -50, 12);
$s2->drawCurve(-49, 12, -48, -10);
$s2->drawCurve(-48, -9, -39, -29);
$s2->drawCurve(-39, -29, -24, -43);
$s2->drawCurve(-24, -43, -2, -51);
$s2->drawCurve(-3, -50, 19, -46);
$s2->drawCurve(29, -73, 72, -37);
$s2->drawCurve(70, -37, 77, 15);
$s2->drawCurve(76, 15, 50, 62);
$s2->drawCurve(49, 61, 1, 78);
$s2->drawLine(0, 1);
$s2->movePenTo(279, 206);
$s2->setLeftFill(0x79, 0x79, 0x79);
$s2->setRightFill(0xff, 0xa8, 0x37);
$s2->drawCurve(0, -19, 13, -12);
$s2->drawCurve(12, -13, 19, 0);
$s2->drawCurve(18, 0, 13, 13);
$s2->drawCurve(12, 12, 1, 19);
$s2->drawCurve(-1, 18, -12, 13);
$s2->drawCurve(-13, 12, -18, 1);
$s2->drawCurve(-19, -1, -12, -12);
$s2->drawCurve(-13, -13, 0, -18);
$s2->movePenTo(640, 0);
$s2->setLeftFill();
$s2->setRightFill(0xcc, 0xcc, 0xcc);
$s2->setLine(20, 0xcc, 0xcc, 0xcc); 
$s2->drawLine(0, 600);
$s2->setLine(20, 0x99, 0x99, 0x99); # raya inferior
$s2->drawLine(-640, 0);
$s2->setLine(20, 0xcc, 0xcc, 0xcc); # raya izq
$s2->drawLine(0, -600);
$s2->setLine(20, 0x99, 0x99, 0x99); # raya sup
$s2->drawLine(640, 0);

$i1 = $boton_debug->add($s1);
$i1->scaleTo(0.5);
$boton_debug->nextFrame();
$boton_debug->remove($i1);
$i1 = $boton_debug->add($s2);
$i1->scaleTo(0.5);
$boton_debug->nextFrame();  
$boton_debug->remove($i1);
$boton_debug->nextFrame();  



$boton_reload = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(424, 154);
$s1->setLeftFill(0x79, 0x79, 0x79);
$s1->setRightFill(0xff, 0xa8, 0x37);
$s1->drawLine(-71, -37);
$s1->drawCurve(-8, -4, -6, 4);
$s1->drawCurve(-7, 4, -1, 9);
$s1->drawLine(-1, 11);
$s1->drawLine(-52, 5);
$s1->drawCurve(-53, 12, -36, 39);
$s1->drawCurve(-37, 39, -6, 56);
$s1->drawCurve(-7, 53, 25, 49);
$s1->drawCurve(23, 47, 49, 26);
$s1->drawCurve(49, 25, 52, -6);
$s1->drawCurve(56, -7, 41, -37);
$s1->drawCurve(42, -37, 12, -55);
$s1->drawCurve(5, -25, -2, -28);
$s1->drawCurve(-2, -12, -6, -7);
$s1->drawCurve(-8, -8, -10, 1);
$s1->drawCurve(-11, -1, -8, 8);
$s1->drawCurve(-9, 8, 1, 11);
$s1->drawCurve(5, 45, -23, 37);
$s1->drawCurve(-24, 37, -43, 14);
$s1->drawLine(-76, -2);
$s1->drawCurve(-38, -14, -22, -33);
$s1->drawCurve(-22, -33, 2, -40);
$s1->drawCurve(2, -41, 25, -30);
$s1->drawCurve(26, -31, 39, -9);
$s1->drawLine(37, -3);
$s1->drawLine(-2, 16);
$s1->drawCurve(0, 8, 8, 5);
$s1->drawCurve(7, 4, 7, -4);
$s1->drawLine(78, -43);
$s1->drawLine(7, -9);
$s1->drawLine(0, -3);
$s1->drawLine(0, -5);
$s1->drawLine(-7, -9);
$s1->movePenTo(496, 135);
$s1->setRightFill(0xcc, 0xcc, 0xcc);
$s1->drawCurve(-34, -34, -44, -20);
$s1->drawCurve(-46, -20, -50, -1);
$s1->drawCurve(-50, -1, -46, 19);
$s1->drawCurve(-44, 17, -35, 34);
$s1->drawCurve(-35, 34, -20, 44);
$s1->drawCurve(-22, 45, -2, 49);
$s1->drawCurve(-2, 50, 18, 46);
$s1->drawCurve(18, 45, 33, 36);
$s1->drawCurve(33, 35, 44, 21);
$s1->drawCurve(45, 21, 49, 3);
$s1->drawCurve(50, 3, 47, -17);
$s1->drawCurve(44, -16, 37, -33);
$s1->drawCurve(36, -33, 22, -43);
$s1->drawCurve(22, -44, 4, -49);
$s1->drawLine(0, -16);
$s1->drawLine(0, -1);
$s1->drawCurve(-1, -50, -19, -45);
$s1->drawCurve(-19, -45, -33, -34);
$s1->movePenTo(539, 310);
$s1->setRightFill(0xff, 0xa8, 0x37);
$s1->drawCurve(-1, 49, -20, 44);
$s1->drawCurve(-22, 44, -37, 32);
$s1->drawCurve(-38, 32, -49, 12);
$s1->drawCurve(-49, 12, -49, -10);
$s1->drawCurve(-47, -9, -40, -29);
$s1->drawCurve(-39, -29, -23, -43);
$s1->drawCurve(-25, -43, -2, -51);
$s1->drawCurve(-3, -50, 19, -46);
$s1->drawCurve(29, -73, 72, -37);
$s1->drawCurve(71, -37, 76, 15);
$s1->drawCurve(76, 15, 51, 62);
$s1->drawCurve(49, 61, 1, 78);
$s1->drawLine(0, 1);
$s1->movePenTo(640, 0);
$s1->setLeftFill();
$s1->setRightFill(0xcc, 0xcc, 0xcc);
$s1->setLine(20, 0xcc, 0xcc, 0xcc);
$s1->drawLine(0, 600);
$s1->setLine(20, 0x99, 0x99, 0x99); # raya inferior
$s1->drawLine(-640, 0);
$s1->setLine(20, 0xcc, 0xcc, 0xcc);
$s1->drawLine(0, -600);
$s1->setLine(20, 0x99, 0x99, 0x99);
$s1->drawLine(640, 0);

### Shape 2 ###
$s2 = new SWF::Shape();
$s2->movePenTo(424, 154);
$s2->setLeftFill(0xff, 0xa8, 0x37);
$s2->setRightFill(0x79, 0x79, 0x79);
$s2->drawLine(-71, -37);
$s2->drawCurve(-8, -4, -6, 4);
$s2->drawCurve(-7, 4, -1, 9);
$s2->drawLine(-1, 11);
$s2->drawLine(-53, 4);
$s2->drawCurve(-52, 13, -36, 39);
$s2->drawCurve(-37, 39, -6, 56);
$s2->drawCurve(-7, 53, 24, 49);
$s2->drawCurve(23, 47, 50, 26);
$s2->drawCurve(49, 25, 52, -6);
$s2->drawCurve(56, -7, 41, -37);
$s2->drawCurve(42, -37, 12, -55);
$s2->drawCurve(5, -25, -2, -28);
$s2->drawCurve(-2, -12, -6, -7);
$s2->drawCurve(-8, -8, -11, 1);
$s2->drawCurve(-10, -1, -8, 8);
$s2->drawCurve(-9, 8, 1, 11);
$s2->drawCurve(5, 45, -23, 37);
$s2->drawCurve(-24, 37, -43, 14);
$s2->drawLine(-76, -2);
$s2->drawCurve(-38, -14, -22, -33);
$s2->drawCurve(-22, -33, 2, -40);
$s2->drawCurve(1, -41, 26, -30);
$s2->drawCurve(25, -31, 40, -9);
$s2->drawLine(37, -3);
$s2->drawLine(-2, 16);
$s2->drawCurve(0, 8, 8, 5);
$s2->drawCurve(7, 4, 7, -4);
$s2->drawLine(78, -43);
$s2->drawLine(7, -9);
$s2->drawLine(0, -3);
$s2->drawLine(0, -5);
$s2->drawLine(-7, -9);
$s2->movePenTo(152, 495);
$s2->setRightFill(0xcc, 0xcc, 0xcc);
$s2->drawLine(60, 39);
$s2->drawCurve(44, 21, 50, 3);
$s2->drawCurve(50, 3, 47, -17);
$s2->drawCurve(44, -16, 37, -33);
$s2->drawLine(46, -55);
$s2->drawLine(12, -21);
$s2->drawLine(10, -22);
$s2->drawLine(16, -71);
$s2->drawLine(0, -16);
$s2->drawLine(0, -1);
$s2->drawCurve(-1, -50, -19, -45);
$s2->drawCurve(-19, -45, -33, -34);
$s2->drawCurve(-34, -34, -44, -20);
$s2->drawCurve(-46, -20, -50, -1);
$s2->drawCurve(-50, -1, -46, 19);
$s2->drawCurve(-44, 17, -35, 34);
$s2->drawLine(-44, 56);
$s2->setRightFill();
$s2->drawLine(-11, 22);
$s2->drawLine(-10, 23);
$s2->setRightFill(0xcc, 0xcc, 0xcc);
$s2->drawLine(-14, 71);
$s2->drawCurve(-2, 50, 18, 46);
$s2->drawLine(35, 62);
$s2->setRightFill();
$s2->drawLine(16, 19);
$s2->drawLine(17, 17);
$s2->setLeftFill(0xcc, 0xcc, 0xcc);
$s2->drawLine(-17, -17);
$s2->drawLine(-16, -19);
$s2->movePenTo(539, 310);
$s2->setLeftFill(0xff, 0xa8, 0x37);
$s2->setRightFill(0x79, 0x79, 0x79);
$s2->drawCurve(-1, 49, -20, 44);
$s2->drawCurve(-22, 44, -37, 32);
$s2->drawCurve(-38, 32, -49, 12);
$s2->drawCurve(-49, 12, -49, -10);
$s2->drawCurve(-47, -9, -40, -29);
$s2->drawCurve(-39, -29, -23, -43);
$s2->drawCurve(-25, -43, -2, -51);
$s2->drawCurve(-3, -50, 19, -46);
$s2->drawCurve(29, -73, 72, -37);
$s2->drawCurve(71, -37, 76, 15);
$s2->drawCurve(76, 15, 51, 62);
$s2->drawCurve(49, 61, 1, 78);
$s2->drawLine(0, 1);
$s2->movePenTo(103, 185);
$s2->setLeftFill();
$s2->setRightFill(0xcc, 0xcc, 0xcc);
$s2->setLine(20, 0xcc, 0xcc, 0xcc);
$s2->drawLine(-12, 22);
$s2->drawLine(-9, 23);
$s2->movePenTo(640, 0);
$s2->drawLine(0, 600);
#$s2->setLine(20, 0x00, 0x00, 0x00);
$s2->setLine(20, 0x99, 0x99, 0x99); # raya inferior
$s2->drawLine(-640, 0);
$s2->setLine(20, 0xcc, 0xcc, 0xcc);
$s2->drawLine(0, -600);
$s2->setLine(20, 0x99, 0x99, 0x99);
$s2->drawLine(640, 0);
$i1 = $boton_reload->add($s1);
$i1->scaleTo(0.5);
$boton_reload->nextFrame();  # end of frame 1
$boton_reload->remove($i1);
$i1 = $boton_reload->add($s2);
$i1->scaleTo(0.5);
$boton_reload->nextFrame();  # end of frame 2
$boton_reload->remove($i1);


$boton_security = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(493, 135);
$s1->setLeftFill(0x79, 0x79, 0x79);
$s1->setRightFill(0xcc, 0xcc, 0xcc);
$s1->drawCurve(-34, -34, -44, -19);
$s1->drawCurve(-45, -21, -50, -1);
$s1->drawCurve(-49, -1, -46, 19);
$s1->drawCurve(-45, 18, -35, 34);
$s1->drawCurve(-35, 33, -20, 44);
$s1->drawCurve(-21, 45, -2, 49);
$s1->drawCurve(-2, 50, 18, 46);
$s1->drawCurve(17, 44, 34, 36);
$s1->drawCurve(33, 35, 43, 21);
$s1->drawCurve(45, 22, 49, 3);
$s1->drawCurve(49, 3, 47, -17);
$s1->drawCurve(45, -17, 36, -32);
$s1->drawCurve(36, -33, 22, -43);
$s1->drawCurve(22, -45, 4, -48);
$s1->drawLine(0, -16);
$s1->drawLine(0, -1);
$s1->drawCurve(-1, -50, -19, -45);
$s1->drawCurve(-19, -45, -33, -34);
$s1->movePenTo(536, 310);
$s1->setRightFill(0xff, 0xa8, 0x37);
$s1->drawCurve(-1, 48, -20, 45);
$s1->drawCurve(-21, 43, -38, 33);
$s1->drawCurve(-37, 31, -49, 13);
$s1->drawCurve(-49, 11, -49, -9);
$s1->drawCurve(-47, -10, -39, -29);
$s1->drawCurve(-39, -29, -24, -42);
$s1->drawCurve(-24, -43, -2, -51);
$s1->drawCurve(-3, -50, 19, -46);
$s1->drawCurve(28, -72, 72, -37);
$s1->drawCurve(71, -37, 76, 15);
$s1->drawCurve(76, 15, 50, 61);
$s1->drawCurve(49, 61, 1, 78);
$s1->drawLine(0, 1);
$s1->movePenTo(446, 290);
$s1->setLeftFill(0xff, 0xa8, 0x37);
$s1->setRightFill(0x79, 0x79, 0x79);
$s1->drawLine(0, 129);
$s1->drawCurve(0, 17, -17, 13);
$s1->drawCurve(-17, 13, -23, 0);
$s1->drawLine(-143, 0);
$s1->drawCurve(-23, 0, -17, -13);
$s1->drawCurve(-16, -13, 0, -17);
$s1->drawLine(0, -129);
$s1->drawCurve(-1, -15, 12, -11);
$s1->drawLine(1, -8);
$s1->drawCurve(7, -50, 32, -35);
$s1->drawCurve(35, -40, 49, 5);
$s1->drawCurve(49, 5, 31, 42);
$s1->drawCurve(25, 35, 4, 46);
$s1->drawCurve(12, 11, 0, 15);
$s1->movePenTo(382, 200);
$s1->setLeftFill(0x79, 0x79, 0x79);
$s1->setRightFill(0xff, 0xa8, 0x37);
$s1->drawCurve(15, 22, 6, 26);
$s1->drawLine(-14, -1);
$s1->drawLine(-143, 0);
$s1->drawLine(-14, 1);
$s1->drawCurve(5, -26, 16, -21);
$s1->drawCurve(25, -35, 40, -1);
$s1->drawCurve(39, -1, 25, 36);
$s1->movePenTo(323, 345);
$s1->drawLine(14, 75);
$s1->drawLine(-40, 0);
$s1->drawLine(14, -75);
$s1->drawCurve(-8, -2, -6, -6);
$s1->drawCurve(-8, -8, 0, -12);
$s1->drawCurve(0, -13, 8, -8);
$s1->drawCurve(9, -9, 12, 1);
$s1->drawCurve(12, -1, 8, 9);
$s1->drawCurve(9, 8, 0, 13);
$s1->drawCurve(0, 12, -9, 8);
$s1->drawLine(-15, 8);
$s1->movePenTo(640, 600);
$s1->setLeftFill();
$s1->setRightFill(0xcc, 0xcc, 0xcc);
#$s1->setLine(20, 0x00, 0x00, 0x00);
$s1->setLine(20, 0x99, 0x99, 0x99); # raya inferior
$s1->drawLine(-640, 0);
$s1->setLine(20, 0xcc, 0xcc, 0xcc);
$s1->drawLine(0, -600);
$s1->setLine(20, 0x99, 0x99, 0x99);
$s1->drawLine(640, 0);
$s1->setLine(20, 0xcc, 0xcc, 0xcc);
$s1->drawLine(0, 600);

### Shape 2 ###
$s2 = new SWF::Shape();
$s2->movePenTo(495, 136);
$s2->setLeftFill(0xff, 0xa8, 0x37);
$s2->setRightFill(0xcc, 0xcc, 0xcc);
$s2->drawCurve(-34, -35, -44, -19);
$s2->drawCurve(-46, -21, -49, -1);
$s2->drawCurve(-50, -1, -46, 19);
$s2->drawCurve(-45, 18, -35, 34);
$s2->drawCurve(-35, 33, -20, 45);
$s2->drawCurve(-21, 45, -2, 49);
$s2->drawCurve(-2, 50, 18, 47);
$s2->drawCurve(17, 44, 34, 36);
$s2->drawCurve(33, 35, 44, 21);
$s2->drawCurve(44, 22, 50, 3);
$s2->drawCurve(49, 3, 47, -17);
$s2->drawCurve(45, -17, 36, -33);
$s2->drawCurve(36, -33, 22, -43);
$s2->drawCurve(22, -44, 4, -49);
$s2->drawLine(1, -16);
$s2->drawLine(0, -1);
$s2->drawCurve(-1, -50, -19, -45);
$s2->drawCurve(-19, -45, -34, -34);
$s2->movePenTo(539, 311);
$s2->setRightFill(0x79, 0x79, 0x79);
$s2->drawCurve(-1, 49, -21, 44);
$s2->drawCurve(-21, 44, -38, 32);
$s2->drawCurve(-37, 32, -50, 12);
$s2->drawLine(-97, 3);
$s2->drawCurve(-48, -10, -39, -29);
$s2->drawCurve(-39, -29, -24, -43);
$s2->drawCurve(-24, -43, -2, -51);
$s2->drawCurve(-3, -50, 19, -47);
$s2->drawCurve(29, -72, 72, -37);
$s2->drawCurve(70, -38, 77, 16);
$s2->drawCurve(76, 15, 50, 62);
$s2->drawCurve(50, 61, 1, 78);
$s2->drawLine(0, 1);
$s2->movePenTo(448, 291);
$s2->setLeftFill(0x79, 0x79, 0x79);
$s2->setRightFill(0xff, 0xa8, 0x37);
$s2->drawLine(0, 129);
$s2->drawCurve(0, 18, -17, 13);
$s2->drawCurve(-17, 12, -23, 1);
$s2->drawLine(-144, 0);
$s2->drawCurve(-23, -1, -17, -12);
$s2->drawCurve(-17, -13, 1, -18);
$s2->drawLine(0, -129);
$s2->drawCurve(-1, -15, 12, -12);
$s2->drawLine(1, -8);
$s2->drawCurve(7, -50, 32, -35);
$s2->drawCurve(36, -40, 49, 6);
$s2->drawCurve(49, 4, 31, 42);
$s2->drawCurve(25, 35, 4, 46);
$s2->drawCurve(12, 12, 0, 15);
$s2->movePenTo(319, 165);
$s2->setLeftFill(0xff, 0xa8, 0x37);
$s2->setRightFill(0x79, 0x79, 0x79);
$s2->drawCurve(39, -1, 26, 37);
$s2->drawCurve(15, 21, 5, 26);
$s2->drawLine(-13, -1);
$s2->drawLine(-144, 0);
$s2->drawLine(-13, 1);
$s2->drawCurve(4, -26, 16, -21);
$s2->drawCurve(26, -35, 39, -1);
$s2->movePenTo(324, 346);
$s2->drawLine(15, 76);
$s2->drawLine(-41, 0);
$s2->drawLine(14, -76);
$s2->drawCurve(-8, -2, -5, -6);
$s2->drawCurve(-9, -9, 0, -12);
$s2->drawCurve(0, -12, 9, -8);
$s2->drawCurve(8, -9, 12, 0);
$s2->drawCurve(12, 0, 9, 9);
$s2->drawLine(9, 20);
$s2->drawCurve(-1, 12, -8, 9);
$s2->drawLine(-16, 8);
$s2->movePenTo(640, 0);
$s2->setLeftFill();
$s2->setRightFill(0xcc, 0xcc, 0xcc);
$s2->drawLine(0, 600);
#$s2->setLine(20, 0x00, 0x00, 0x00);
$s2->setLine(20, 0x99, 0x99, 0x99); # raya inferior
$s2->drawLine(-640, 0);
$s2->setLine(20, 0xcc, 0xcc, 0xcc);
$s2->drawLine(0, -600);
$s2->setLine(20, 0x99, 0x99, 0x99);
$s2->drawLine(640, 0);

$i1 = $boton_security->add($s1);
$i1->scaleTo(0.5);
$boton_security->add(new SWF::Action("stop();"));
$boton_security->nextFrame();  # end of frame 1
$boton_security->remove($i1);
$i1 = $boton_security->add($s2);
$i1->scaleTo(0.5);
$boton_security->add(new SWF::Action("stop();"));
$boton_security->nextFrame();  # end of frame 1
$boton_security->remove($i1);

$boton_security_unlock = new SWF::Sprite();
### Shape 1 ###
$s1 = new SWF::Shape();
$s1->movePenTo(493, 135);
$s1->setLeftFill(0x79, 0x79, 0x79);
$s1->setRightFill(0xcc, 0xcc, 0xcc);
$s1->drawCurve(-34, -34, -44, -19);
$s1->drawCurve(-45, -21, -50, -1);
$s1->drawCurve(-49, -1, -46, 19);
$s1->drawCurve(-45, 18, -35, 34);
$s1->drawCurve(-35, 33, -20, 44);
$s1->drawCurve(-21, 45, -2, 49);
$s1->drawCurve(-2, 50, 18, 46);
$s1->drawCurve(17, 44, 34, 36);
$s1->drawCurve(33, 35, 43, 21);
$s1->drawCurve(45, 22, 49, 3);
$s1->drawCurve(49, 3, 47, -17);
$s1->drawCurve(45, -17, 36, -32);
$s1->drawCurve(36, -33, 22, -43);
$s1->drawCurve(22, -45, 4, -48);
$s1->drawLine(0, -16);
$s1->drawLine(0, -1);
$s1->drawCurve(-1, -50, -19, -45);
$s1->drawCurve(-19, -45, -33, -34);
$s1->movePenTo(536, 310);
$s1->setRightFill(0xff, 0xa8, 0x37);
$s1->drawCurve(-1, 48, -20, 45);
$s1->drawCurve(-21, 43, -38, 33);
$s1->drawCurve(-37, 31, -49, 13);
$s1->drawCurve(-49, 11, -49, -9);
$s1->drawCurve(-47, -10, -39, -29);
$s1->drawCurve(-39, -29, -24, -42);
$s1->drawCurve(-24, -43, -2, -51);
$s1->drawCurve(-3, -50, 19, -46);
$s1->drawCurve(28, -72, 72, -37);
$s1->drawCurve(71, -37, 76, 15);
$s1->drawCurve(76, 15, 50, 61);
$s1->drawCurve(49, 61, 1, 78);
$s1->drawLine(0, 1);
$s1->movePenTo(446, 290);
$s1->setLeftFill(0xff, 0xa8, 0x37);
$s1->setRightFill(0x79, 0x79, 0x79);
$s1->drawLine(0, 129);
$s1->drawCurve(0, 17, -17, 13);
$s1->drawCurve(-17, 13, -23, 0);
$s1->drawLine(-143, 0);
$s1->drawCurve(-23, 0, -17, -13);
$s1->drawCurve(-16, -13, 0, -17);
$s1->drawLine(0, -129);
$s1->drawCurve(-1, -15, 12, -11);
$s1->drawLine(1, -8);
$s1->drawCurve(7, -50, 32, -35);
$s1->drawCurve(35, -40, 49, 5);
$s1->drawCurve(49, 5, 31, 42);
$s1->drawCurve(25, 35, 4, 46);
$s1->drawCurve(12, 11, 0, 15);
$s1->movePenTo(382, 200);
$s1->setLeftFill(0x79, 0x79, 0x79);
$s1->setRightFill(0xff, 0xa8, 0x37);
$s1->drawCurve(15, 22, 6, 26);
$s1->drawLine(-14, -1);
$s1->drawLine(-143, 0);
$s1->drawLine(-14, 1);
$s1->drawCurve(5, -26, 16, -21);
$s1->drawCurve(25, -35, 40, -1);
$s1->drawCurve(39, -1, 25, 36);
$s1->movePenTo(323, 345);
$s1->drawLine(14, 75);
$s1->drawLine(-40, 0);
$s1->drawLine(14, -75);
$s1->drawCurve(-8, -2, -6, -6);
$s1->drawCurve(-8, -8, 0, -12);
$s1->drawCurve(0, -13, 8, -8);
$s1->drawCurve(9, -9, 12, 1);
$s1->drawCurve(12, -1, 8, 9);
$s1->drawCurve(9, 8, 0, 13);
$s1->drawCurve(0, 12, -9, 8);
$s1->drawLine(-15, 8);
$s1->movePenTo(640, 600);
$s1->setLeftFill();
$s1->setRightFill(0xcc, 0xcc, 0xcc);
$s1->setLine(20, 0x99, 0x99, 0x99);
$s1->drawLine(-640, 0);
$s1->setLine(20, 0xcc, 0xcc, 0xcc);
$s1->drawLine(0, -600);
$s1->setLine(20, 0x99, 0x99, 0x99);
$s1->drawLine(640, 0);
$s1->setLine(20, 0xcc, 0xcc, 0xcc);
$s1->drawLine(0, 600);
# Agregado
$s1b = new SWF::Shape();
$s1b->movePenTo(430,135);
$s1b->setLine(0,0xFF,0xa8,0x37);
$s1b->setRightFill(0xFF,0xa8,0x37,0xFF);
$s1b->drawLine(0,110);
$s1b->drawLine(-110,0);
$s1b->drawLine(0,-110);
$s1b->drawLine(110,0);


### Shape 2 ###
$s2 = new SWF::Shape();
$s2->movePenTo(495, 136);
$s2->setLeftFill(0xff, 0xa8, 0x37);
$s2->setRightFill(0xcc, 0xcc, 0xcc);
$s2->drawCurve(-34, -35, -44, -19);
$s2->drawCurve(-46, -21, -49, -1);
$s2->drawCurve(-50, -1, -46, 19);
$s2->drawCurve(-45, 18, -35, 34);
$s2->drawCurve(-35, 33, -20, 45);
$s2->drawCurve(-21, 45, -2, 49);
$s2->drawCurve(-2, 50, 18, 47);
$s2->drawCurve(17, 44, 34, 36);
$s2->drawCurve(33, 35, 44, 21);
$s2->drawCurve(44, 22, 50, 3);
$s2->drawCurve(49, 3, 47, -17);
$s2->drawCurve(45, -17, 36, -33);
$s2->drawCurve(36, -33, 22, -43);
$s2->drawCurve(22, -44, 4, -49);
$s2->drawLine(1, -16);
$s2->drawLine(0, -1);
$s2->drawCurve(-1, -50, -19, -45);
$s2->drawCurve(-19, -45, -34, -34);
$s2->movePenTo(539, 311);
$s2->setRightFill(0x79, 0x79, 0x79);
$s2->drawCurve(-1, 49, -21, 44);
$s2->drawCurve(-21, 44, -38, 32);
$s2->drawCurve(-37, 32, -50, 12);
$s2->drawLine(-97, 3);
$s2->drawCurve(-48, -10, -39, -29);
$s2->drawCurve(-39, -29, -24, -43);
$s2->drawCurve(-24, -43, -2, -51);
$s2->drawCurve(-3, -50, 19, -47);
$s2->drawCurve(29, -72, 72, -37);
$s2->drawCurve(70, -38, 77, 16);
$s2->drawCurve(76, 15, 50, 62);
$s2->drawCurve(50, 61, 1, 78);
$s2->drawLine(0, 1);
$s2->movePenTo(448, 291);
$s2->setLeftFill(0x79, 0x79, 0x79);
$s2->setRightFill(0xff, 0xa8, 0x37);
$s2->drawLine(0, 129);
$s2->drawCurve(0, 18, -17, 13);
$s2->drawCurve(-17, 12, -23, 1);
$s2->drawLine(-144, 0);
$s2->drawCurve(-23, -1, -17, -12);
$s2->drawCurve(-17, -13, 1, -18);
$s2->drawLine(0, -129);
$s2->drawCurve(-1, -15, 12, -12);
$s2->drawLine(1, -8);
$s2->drawCurve(7, -50, 32, -35);
$s2->drawCurve(36, -40, 49, 6);
$s2->drawCurve(49, 4, 31, 42);
$s2->drawCurve(25, 35, 4, 46);
$s2->drawCurve(12, 12, 0, 15);
$s2->movePenTo(319, 165);
$s2->setLeftFill(0xff, 0xa8, 0x37);
$s2->setRightFill(0x79, 0x79, 0x79);
$s2->drawCurve(39, -1, 26, 37);
$s2->drawCurve(15, 21, 5, 26);
$s2->drawLine(-13, -1);
$s2->drawLine(-144, 0);
$s2->drawLine(-13, 1);
$s2->drawCurve(4, -26, 16, -21);
$s2->drawCurve(26, -35, 39, -1);
$s2->movePenTo(324, 346);
$s2->drawLine(15, 76);
$s2->drawLine(-41, 0);
$s2->drawLine(14, -76);
$s2->drawCurve(-8, -2, -5, -6);
$s2->drawCurve(-9, -9, 0, -12);
$s2->drawCurve(0, -12, 9, -8);
$s2->drawCurve(8, -9, 12, 0);
$s2->drawCurve(12, 0, 9, 9);
$s2->drawLine(9, 20);
$s2->drawCurve(-1, 12, -8, 9);
$s2->drawLine(-16, 8);
$s2->movePenTo(640, 0);
$s2->setLeftFill();
$s2->setRightFill(0xcc, 0xcc, 0xcc);
$s2->drawLine(0, 600);
$s2->setLine(20, 0x99, 0x99, 0x99);
$s2->drawLine(-640, 0);
$s2->setLine(20, 0xcc, 0xcc, 0xcc);
$s2->drawLine(0, -600);
$s2->setLine(20, 0x99, 0x99, 0x99);
$s2->drawLine(640, 0);
# Agregado
$s2b = new SWF::Shape();
$s2b->movePenTo(430,135);
$s2b->setLine(0,0x79,0x79,0x79);
$s2b->setRightFill(0x79,0x79,0x79,0xFF);
$s2b->drawLine(0,110);
$s2b->drawLine(-110,0);
$s2b->drawLine(0,-110);
$s2b->drawLine(110,0);

$i1 = $boton_security_unlock->add($s1);
$i1->scaleTo(0.5);
$i1 = $boton_security_unlock->add($s1b);
$i1->scaleTo(0.5);
$boton_security_unlock->add(new SWF::Action("stop();"));
$boton_security_unlock->nextFrame();  # end of frame 1
$boton_security_unlock->remove($i1);
$i1 = $boton_security_unlock->add($s2);
$i1->scaleTo(0.5);
$i1 = $boton_security_unlock->add($s2b);
$i1->scaleTo(0.5);
$boton_security_unlock->add(new SWF::Action("stop();"));
$boton_security_unlock->nextFrame();  # end of frame 1
$boton_security_unlock->remove($i1);

# Exports all Movieclips for Actionscript
$movie->addExport($ledcolor,"ledcolor");
$movie->addExport($ledsombra,"ledsombra");
$movie->addExport($ledbrillo,"ledbrillo");
$movie->addExport($fle,         "arrow");
$movie->addExport($i_icon1,     "telefono1");
$movie->addExport($i_icon2,     "telefono2");
$movie->addExport($telefono3,   "telefono3");
$movie->addExport($telefono4,   "telefono4");
$movie->addExport($conference2, "telefono5");
$movie->addExport($conference1, "telefono6");
$movie->addExport($envelope,    "sobre");
# $movie->addExport($statusbar,   "logtext");
$movie->addExport($extrainfo,   "infotext");
$movie->addExport($boton_ayuda, "boton_ayuda");
$movie->addExport($boton_debug, "boton_debug");
$movie->addExport($boton_reload,"boton_reload");
$movie->addExport($boton_security,"boton_security");
$movie->addExport($boton_security_unlock,"boton_security_unlock");
$movie->addExport($option,"option");
$movie->writeExports();


# Adds ActionScript
$movie->add(new SWF::Action(<<"EndOfActionScript"));

/*
XMLSocket.prototype.onData = function(msg)
{
	trace("MSG: " + msg)
	msgArea.htmlText += msg
}
*/

function conecta() {
	_global.sock = new XMLSocket;
	_global.sock.onConnect = handleConnect;
	_global.sock.onClose = handleDisconnect;
	_global.sock.onXML = handleXML;
    if(_global.port   == undefined) {
        _global.port = 4445;
    }
    if(_global.server == undefined) {
	    _global.sock.connect(null, _global.port);
        _global.server_print = "default";
    } else {
	    _global.sock.connect(_global.server, _global.port);
        _global.server_print = _global.server;
    }
}

function logea(texto) {
	if(buttondebug._visible != true) {
		return;
	}
    var fecha = new Date();
    var hora = fecha.getHours();
    var minutos = fecha.getMinutes();
    var segundos = fecha.getSeconds();
    if (hora<10) {
        hora = "0"+hora;
    }
    if (minutos<10) {
        minutos = "0"+minutos;
    }
    if (segundos<10) {
        segundos = "0"+segundos;
    }
    var textologea = hora+":"+minutos+":"+segundos+" "+texto;
    _global.loglines.push(textologea);
    //if (_global.loglines.length>35) {
        _global.loglines.shift();
    //}
    _level0.log.Field1.text = "";
	var acount=-1;
	while (++acount < _global.loglines.length) {
//    for (var acount=0; acount<_global.loglines.length; acount++) {
        _level0.log.Field1.text = _level0.log.Field1.text+_global.loglines[acount]+"\n";
    }
    log.logcontent.scroll = log.logcontent.maxscroll;
};


function handleConnect(connectionStatus){

    if (connectionStatus) {
        for (var a in _root) {
            if (typeof (_root[a]) == "movieclip") {
                if (a.substring(0, 10) == "rectangulo") {
                    for (var b in _root[a]) {
                        if (_root[a][b]._name.substring(0, 7) == "casicol") {
							var numero=ExtraeNumeroClip(_root[a][b]);
                            _root[a][b].changeledcolor(0,_global.colorlibre[numero],_global.color[1]); 
                        }
                    } 
                }
            }
        }
        logea("Connected to server "+server_print+" on port "+port);

        _global.reconecta = 0;
		if(_global.enable_crypto==1) {
        	envia_comando("contexto", 0, 0);
		} else {
        	envia_comando("contexto", 1, 0);
		}
		if(restrict != undefined) {
			envia_comando("restrict",restrict,0);
		}
    } else {
        logea("Error  connecting to "+server);
		logea(" on port "+port);
        for (var a in _root) {
            if (typeof (_root[a]) == "movieclip") {
                if (a.substring(0, 10) == "rectangulo") {
                    for (var b in _root[a]) {
                        if (_root[a][b]._name.substring(0, 7) == "casicol") {
							var numero=ExtraeNumeroClip(_root[a][b]);
                            _root[a][b].changeledcolor(3,_global.colorlibre[numero],_global.color[1]);
                        }
                    }
                }
            }
        }
        _global.reconecta = 1;
    }
}

function handleXML(doc){
 	var e = doc.firstChild;
	if (e != null) {
		if (e.nodeName == "response") {
		    var numeroboton = e.attributes.btn; // btn is the button number
			var comando     = e.attributes.cmd;
			var textofinal  = e.attributes.data;

			if (_global.key != undefined) {
				if(_global.enable_crypto == 1) {
					comando    = decrypt(comando,    _global.key);
					if (textofinal.length > 0) {
						textofinal = decrypt(textofinal, _global.key);
					} 
				} 
			} 

			logea(numeroboton+"|"+comando+"|"+textofinal.substring(0,20));

			var botonparte = numeroboton.split("@");
			var boton_numero = botonparte[0];
			var boton_contexto = botonparte[1];
			var timernumber = 0;
			if (boton_contexto == undefined) {
				boton_contexto = "";
			}
			if (_root.context == undefined) {
				_root.context = "";
			}
			if (comando == "key") {
				_global.key = textofinal;
				return;
			}
			if (comando == "incorrect") {
				_global.authorized = false;
				_root.codebox._visible = true;
				Selection.setFocus(_root.codebox.claveform);
				_root.codebox.swapDepths(_root.log);
				SecurityCode_Unlocked();
				return;
			}
			if (comando == "correct") {
				_global.authorized = true;
				SecurityCode_Locked();
				return;
			}
			if (comando == "reload") {
				logea("Recarga!");
				_root.recarga();
				// return;
			}

			if (comando == "showdetails") {
				var myclip = eval('_level0.rectangulo'+boton_numero+'.flecha'+boton_numero);
				_root.displaydetails(myclip);
			}

			if (comando == "restrict") {
				_global.restrict = numeroboton;
                _global.mybutton = numeroboton;
				logea("Set restriction for button "+_global.restrict);
				var myresa = eval('_root.resaltado'+_global.restrict);
				myresa._visible = true;
				return;
			}
			if (comando == "version") {
				logea("Version "+textofinal);
				logea("Top "+_root._y);
				logea("Left "+_root._x);
				if(textofinal != _global.swfversion) 
				{
					_global.statusline=vr.version_mismatch;
				} else {
					_global.statusline="";
				}
			}

			if (_root.context == boton_contexto) {
				var botonazo = eval("rectangulo"+boton_numero+".casicol"+boton_numero);
				var statusclid = eval("rectangulo"+boton_numero+".statusprint"+boton_numero);
				var timerprint = eval("rectangulo"+boton_numero+".timer"+boton_numero);
				var flechita = eval("rectangulo"+boton_numero+".flecha"+boton_numero);
				var telefonito = eval("rectangulo"+boton_numero+".tele"+boton_numero);
				var sobrecito = eval("rectangulo"+boton_numero+".sobrecito"+boton_numero);
				if (_global.rectanguloprendido!=0) {
					makeStatus(_global.rectanguloprendido);
				}

				if (comando == "setalpha") {
					var myboton = eval('_root.rectangulo'+boton_numero);
				    myboton._alpha = textofinal;
				}

				if (comando == "flip") {
					var myboton = eval('_root.rectangulo'+boton_numero);
				    myboton.flip(textofinal);
				}

				if (comando == "monitor") {
					createCircle(_root["rectangulo"+boton_numero]["circle"+boton_numero],11,0,0,'0xff0000');
				}
				if (comando == "stopmonitor") {
					createCircle(_root["rectangulo"+boton_numero]["circle"+boton_numero],11,0,0,'0x00ff00');
				}

				if (comando == "settext") {
					_global.ipboton[boton_numero]=textofinal;
					setclid(statusclid,textofinal);
				}

				if (comando == "setstatus") {
					_global.texto_tip[boton_numero] = textofinal;
				}

				if (comando == "setlabel") {
					set_button_label_text(boton_numero,textofinal);
					//var botref   = eval("rectangulo"+boton_numero+".textoprint");
					//var botshref = eval("rectangulo"+boton_numero+".textosh");
					//botref.text   = textofinal;
					//botshref.text = textofinal;
				}

				if (comando == "fopledcolor") {
					if(textofinal!="")
					{
						var partes = textofinal.split("^");
						var fcolor = partes[0];
						var fstate = Number(partes[1]);

						if(fcolor == "ledcolor_paused") {
							fcolor = ledcolor_paused;
						}
						if(fcolor == "ledcolor_agent") {
							fcolor = ledcolor_agent;
						}
						_global.color[fstate]=fcolor;
						if(fstate!=1) {
							_global.colorlibre[boton_numero]=fcolor;
						} 
						_global.color[fstate] = fcolor;
						botonazo.changeledcolor(0,_global.colorlibre[boton_numero],_global.color[1]);
					}
				}
				if (comando == "foppopup") {
					logea("fop popup");
					if(textofinal!="")
					{
						var partes = textofinal.split("^");
						var url    = partes[0];
						var target = partes[1];
						var posi   = partes[2];

						if(posi == "" || posi == mybutton) {

                            popup_window(url,target);

							/*
							partes = url.split("?");
							url        = partes[0];
							parametros = partes[1];

							logea("url "+url);
							logea("parametros "+parametros);
							logea("target "+target);
							logea("posi "+posi);

							var cadauno = parametros.split("&");


							if (url != "") {
								if (target == undefined || target == "") {
									target = "_self";
								}
								var c = new LoadVars();
	
								for (var aget in cadauno) {
									partes = cadauno[aget].split("=");
									c[partes[0]]=partes[1];
									logea("c."+partes[0]);
									logea("es igual a "+partes[1]);
								}
								c.send(url, target, 'GET');
								logea("Abro "+url+" en target "+target);
							}
							*/
						}
					}
				}

				if (comando == "state") {

					if(textofinal == "ringing") {
						botonazo.changeledcolor(3,_global.colorlibre[boton_numero],_global.color[1]);
						if (enable_animation == 1) {
							telefonito.shake(_global.shakepixels);
						}
					}

					if(textofinal == "free") {
						createCircle(_root["rectangulo"+boton_numero]["circle"+boton_numero],11,0,0,'0x00ff00');
						botonazo.changeledcolor(0,_global.colorlibre[boton_numero],_global.color[1]);
						if (enable_animation == 1) {
							telefonito.shake();
							telefonito.gotoAndStop(1);
						}
					}
				
					if(textofinal == "busy") {
						_root["rectangulo"+boton_numero]._alpha = 100;
	                    _root["resaltado"+boton_numero]._alpha = 100;
						botonazo.changeledcolor(1,_global.colorlibre[boton_numero],_global.color[1]);
						if (enable_animation == 1) {
							telefonito.shake();
							telefonito.gotoAndStop(2);
						}
						flechita._visible = true;
					}
				}

				if (comando == "meetmemute") {
					_root["rectangulo"+boton_numero]._alpha = 40;
					logea("muted "+boton_numero);
					_global.meetmemute[boton_numero]=0;
					statusclid.text = "Conference "+_global.meetmeroom[boton_numero]+" muted";
				}
				
				if (comando == "meetmeunmute") {
					_root["rectangulo"+boton_numero]._alpha = 100;
					logea("Unmuted "+boton_numero);
					_global.meetmemute[boton_numero] = 1;
					statusclid.text = "Conference "+_global.meetmeroom[boton_numero];
				}

				if (comando.substring(0,11) == "changelabel") {
					var changeled = comando.substring(11,12);
					var botref   = eval("rectangulo"+boton_numero+".textoprint");
					var botshref = eval("rectangulo"+boton_numero+".textosh");
					var cual = eval("rectangulo"+boton_numero);
					var casillero = eval("rectangulo"+boton_numero+".casilla"+boton_numero);
					if(textofinal == "original") {
						labeltexto=_global.labels[boton_numero];
					    _global.colorlibre[boton_numero] = _global.color[0];
					} else if (textofinal == "labeloriginal") {
						labeltexto=_global.labels[boton_numero];
					} else if (textofinal == ".") {
						labeltexto=botref.text;
						if(changeled == 1) {
					    	_global.colorlibre[boton_numero] = _global.color[2];
						}
					} else {
						labeltexto=textofinal;
						if(changeled == 1) {
					    	_global.colorlibre[boton_numero] = _global.color[2];
						}
					}
					if(changeled == 1) {
						if(_global.valorchangeledcolor[boton_numero] == "0") {
							botonazo.changeledcolor(0,_global.colorlibre[boton_numero],_global.color[1],boton_numero);
						} else {
							 logea("esta ocupado "+boton_numero+" no cambio led '"+_global.valorchangeledcolor[boton_numero]+"'");
						}
						
					}
					set_button_label_text(boton_numero,labeltexto);
					//botref.text   = labeltexto;
					//botshref.text = labeltexto;
					return;
				}

				if (comando == "voicemailcount") {
					_global.texto_mail[boton_numero] = textofinal;
					return;
				}
				if (comando.substr(0, 4) == "info") {
					var texto = base64_decode(textofinal);
					var queue = comando.substr(4);
					if(queue != "" ) {
						if(_global.queuemember[boton_numero]==undefined) {
							_global.queuemember[boton_numero]="";
						}
						if(queue=="qstat") {
							queuemember[boton_numero] = new Object();
					    	queuemember[boton_numero]["qstat"]=texto;
						} else if (queue=="qstat2") {
							var lugar = queuemember[boton_numero]["qstat"].indexOf("Agents Logged");
							var str1= queuemember[boton_numero]["qstat"].slice(0,lugar);
							str1 = str1+texto;
					    	queuemember[boton_numero]["qstat"]=str1;
						} else {
							var lineas = texto.split("\n");
							for (val in lineas) {
								campos = lineas[val].split("=");
								if(Trim(campos[0])=="CallsTaken") {
									var id1 = eval("queuemember."+boton_numero);
									if(typeof(id1)!='object') {
									   queuemember[boton_numero] = new Object();
									}
					    			queuemember[boton_numero][queue] =  vr.tab_queue_text + ": "+queue+"\n"+vr.calls_taken_text+": "+campos[1]+"\n\n";
								}
							}
						}
					} else {
						_global.st_direction[boton_numero] = texto;
					}
					if(flechita._visible == false) {
						flechita._visible = true;
						flechita.gotoAndStop(3);
					}
					return;
				}
				if (comando == "meetmeuser") {
					partes = textofinal.split(",");
					_global.meetmemember[boton_numero] = partes[0];
					_global.meetmeroom[boton_numero] = partes[1];
					_global.meetmemute[boton_numero] = 1;
					return;
				}
				if (comando == "desocupado" || comando == "corto") {
					createCircle(_root["rectangulo"+boton_numero]["circle"+boton_numero],11,0,0,'0x00ff00');
					_global.meetmemember[boton_numero] = 0;
					_global.meetmeroom[boton_numero] = 0;
					_root["rectangulo"+boton_numero]._alpha = 100;
					botonazo.changeledcolor(0,_global.colorlibre[boton_numero],_global.color[1],boton_numero);
					if (enable_animation == 1) {
						telefonito.shake();
						telefonito.gotoAndStop(1);
					}

					_global.st_duration[boton_numero] = eval("rectangulo"+boton_numero+".timer"+boton_numero+".text");
					_global.texto_tip[boton_numero] = "";
					timerprint.text = "";

					// logea(boton_numero+" pongo timer_type stop y valor 0");
					timer_type[boton_numero] = "STOP";
					inicio_timer[boton_numero] = 0;

					//if (_global.ipboton[boton_numero] != undefined) {
					//	setclid(statusclid,_global.ipboton[boton_numero]);
					//} else {
						statusclid.text = "";  
					//}

					flechita.gotoAndStop(3);
					linkeado[boton_numero]="";	

					return;
				}
				if (comando == "timeout") {
					var timeout = Number(textofinal);
					inicio_timer[boton_numero] = Math.floor(getTimer());
					mytimeout = Math.floor(timeout)*1000;
					inicio_timer[boton_numero] += mytimeout;
					timer_type[boton_numero] = "DOWN";
					logea("TIMEOUT "+timer_type[boton_numero]);
				}
				if (comando == "linked") {
					if (textofinal.indexof("@") == -1) {
						sdo_boton = textofinal;
					} else {
						var boton2parte = textofinal.split("@");
						sdo_boton = boton2parte[0];
					}
					linkeado[boton_numero]=sdo_boton;	
					return;
				}
				if (comando == "clidnum") {
					var clidnum = base64_decode(textofinal);
					_global.clidnumber[boton_numero]=clidnum;
				}
				if (comando == "clidname") {
					//var clidname = base64_decode(textofinal);
					_global.clidname[boton_numero]=textofinal;
					//var clidname = base64_decode(textofinal);
				}
				if (comando == "setvar") {
					if(_global.chanvars[boton_numero].length == undefined ) {
						_global.chanvars[boton_numero] = new Array;
					} 
					chanvars[boton_numero].push(textofinal);
				}
	
				if (comando == "ringing") {
//					if (timer_type[boton_numero]!="UP") {
						inicio_timer[boton_numero]=getTimer();
						botonazo.changeledcolor(3,_global.colorlibre[boton_numero],_global.color[1],boton_numero);
						if (enable_animation == 1) {
							telefonito.shake(_global.shakepixels);
						}
						_global.texto_tip[boton_numero] = textofinal;

						var clnumber = extract_text_inside_delimiters(textofinal,"[","]");
						if(clnumber != "") {
							setclid(statusclid,clnumber);
							_global.st_originclid[boton_numero] = clnumber;
							_global.st_destinationclid[boton_numero] = undefined;
						}
						flechita._visible = true;
						flechita.gotoAndStop(1);

						// CRM, redirige a url 
						if (boton_numero == mybutton) {
							if (url != "") {
								if (target == "") {
									target = "_self";
								}
								var c = new LoadVars();
								//c.clid = clidnumber;
								c.clid      = _global.clidnumber[boton_numero];
								c.clidname  = _global.clidname[boton_numero];

								var a=-1;
								while (++a < chanvars[boton_numero].length) {
									var datossplit = chanvars[boton_numero][a].split("=");
									var mivar = datossplit[0];
									var mival = datossplit[1];
									c[mivar]=mival;
									logea("mivar "+mivar+" has val "+mival);
								}
								delete chanvars[boton_numero];
								c.send(url, target, 'GET');
								logea("Open url "+url+" in target "+target+" with clid "+c.clid);
							}
						} else {
								logea("No popup mybutton "+mybutton+" <> boton_numero "+boton_numero);
						}
						return;
//					}
					return;
				}
				if (comando == "voicemail") {
					if (textofinal == "1") {
						sobrecito._visible = true;
						sobrecito._alpha = 100;
//						sobrecito.glow(1);
					} else {
						sobrecito._visible = true;
						sobrecito._alpha = _global.nomailalpha;
//						sobrecito.glow(0);
					}
					return;
				}

				if (comando == "park") {
					var mytext = extract_text_inside_delimiters(textofinal,"[","]");
					setclid(statusclid,mytext);
					var mytimeout = extract_text_inside_delimiters(textofinal,"(",")");
					inicio_timer[boton_numero] = Math.floor(getTimer());
					mytimeout = Math.floor(mytimeout)*1000;
					inicio_timer[boton_numero] += mytimeout;
					timer_type[boton_numero] = "DOWN";
					_global.st_destinationclid[boton_numero] = mytext;
					_global.st_originclid[boton_numero] = undefined;
					botonazo.changeledcolor(3,_global.colorlibre[boton_numero],_global.color[1],boton_numero);
					return;
				}

				if (comando == "ip") {
					_global.ipboton[boton_numero]=textofinal;
					if(statusclid.text == "") {
						setclid(statusclid,textofinal);
					}
				};

				if (comando == "state") {
					if(textofinal == "busy") {
						botonazo.changeledcolor(1,_global.colorlibre[boton_numero],_global.color[1],boton_numero);
						return;
					}
				}

				if (comando.substr(0, 7) == "ocupado") {
					var flecha_frame = comando.substr(7, 1);
					if (flecha_frame == 3) {				// Parked channel
						var mytext = extract_text_inside_delimiters(textofinal,"[","]");
						setclid(statusclid,mytext);
						_global.st_destinationclid[boton_numero] = mytext;
						_global.st_originclid[boton_numero] = undefined;
						botonazo.changeledcolor(3,_global.colorlibre[boton_numero],_global.color[1],boton_numero);
						return;
					}

					flecha_frame = Number(flecha_frame);
					_root["rectangulo"+boton_numero]._alpha = 100;
                    _root["resaltado"+boton_numero]._alpha = 100;
					botonazo.changeledcolor(1,_global.colorlibre[boton_numero],_global.color[1],boton_numero);
					if (enable_animation == 1) {
						telefonito.shake();
						telefonito.gotoAndStop(2);
					}
					flechita._visible = true;
					if (flechita._currentframe>=1) {
						if (textofinal != undefined) {
							
							var myclid  = extract_text_inside_delimiters(textofinal,"[","]");
							if(myclid != "") {
								setclid(statusclid,myclid);
								_global.st_destinationclid[boton_numero] = myclid;
								_global.st_originclid[boton_numero] = undefined;
							}

							//var timernumber = extract_text_inside_delimiters(textofinal,"(",")");
							//if (isNaN(timernumber)) { timernumber = "0"; }
                            //timernumber = Number(timernumber);  
					
						}
					}
					if (flecha_frame>0) {
						flechita.gotoAndStop(flecha_frame);
						_global.st_direction[boton_numero] = flecha_frame;
					}

					if (statusclid.text.substring(0, 6) == "Parked") {
						setclid(statusclid,"");
					}
					if (textofinal != "") {
						_global.texto_tip[boton_numero] = textofinal;
					}
					
					return;
				}

				if (comando == "settimer") {
					var timerpartes = textofinal.split("@");
					var segundos = Number(timerpartes[0]);
					if (isNaN(segundos)) {
						segundos=0;
					}
					var type     = timerpartes[1];
					if(type == undefined) {
						type="UP";
					}

					if(timer_type[boton_numero] != type)
					{
					// logea(boton_numero+" cambio de tipo vino "+type+" y era "+timer_type[boton_numero]);
						if(type=="DOWN")
						{
							inicio_timer[boton_numero] = getTimer()+(segundos*1000);
						} else if (type == "UP") {
							inicio_timer[boton_numero] = getTimer()-(segundos*1000);
						} else if (type == "IDLE") {
							inicio_timer[boton_numero] = getTimer()-(segundos*1000);
						} else if (type == "STOP") {
							inicio_timer[boton_numero] = 0;
							logea("tipo stop, pongo inicio_timer en cero");
						} 
					} else {
					//	logea(boton_numero+" timer tipo no cambio era igual "+type);
					}
					timer_type[boton_numero]=type;
				}
	
				if (comando == "noregistrado") {
					_root["rectangulo"+boton_numero]._alpha = dimm_noregister;
					_root["resaltado"+boton_numero]._alpha = dimm_noregister;
					_global.texto_tip[boton_numero] = "";
					return;
				}

				if (comando == "unreachable") {
					_root["rectangulo"+boton_numero]._alpha = dimm_lagged;
					_root["resaltado"+boton_numero]._alpha = dimm_lagged;
					_global.texto_tip[boton_numero] = "";
					return;
				}

				if (comando == "registrado") {
					if( _global.meetmemember[boton_numero]==0 || _global.meetmemember[boton_numero] == undefined ) {
						_root["rectangulo"+boton_numero]._alpha = 100;
						_root["resaltado"+boton_numero]._alpha = 100;
					} else {
						logea("No alpha muted o unmuted "+boton_numero);
						logea(_global.meetmemember[boton_numero]);
					}
					return;
				}
			}
			// endif root.context
		}
		// endif == response
	}
	// endiff e != null
}

function setclid(statusclid,textofinal) {
	statusclid.text = textofinal;
	if(_global.clid_centered == 1) {
		clidExtent = fmtClid.getTextExtent ( textofinal );
		cordx = (_global.ancho_a_centrar - clidExtent.textFieldWidth) / 2;
		if(cordx<0) { cordx = 0; }
		statusclid._x = cordx;
	}
}

function popup_window(url,target) {
	logea("popup");
    if (url != "") {
        if (target == "") {
            target = "_self";
        }

        partes = url.split("?");
        url        = partes[0];
        parametros = partes[1];

        var c = new LoadVars();

 		var cadauno = parametros.split("&");

        for (var aget in cadauno) {
           partes = cadauno[aget].split("=");
           c[partes[0]]=partes[1];
        }

        c.send(url, target, 'GET');
        logea("Open url "+url+" in target "+target);
    }
}

function set_button_label_text(btn_number,text) {

	var botref   = eval("rectangulo"+btn_number+".textoprint");
	var botshref = eval("rectangulo"+btn_number+".textosh");

	var lTextExtent = fmtLabel.getTextExtent ( text );
	var lWidth  = lTextExtent.textFieldWidth + _global.label_extent_x;
	var lHeight = lTextExtent.textFieldHeight + _global.label_extent_y;

	botref._width   = lWidth;
	botref._height  = lHeight;
	botref.text   = text;

	botshref._width = lWidth;
	botshref._height= lHeight;
	botshref.text = text;
}


function extract_text_inside_delimiters(inputText,start_char,end_char) {
	var outputText="";
	var start_delimiter = inputText.lastIndexOf(start_char);
	start_delimiter++;
	if (start_delimiter>0) {
    	var end_delimiter = inputText.indexOf(end_char, start_delimiter);
        outputText = inputText.substring(start_delimiter, end_delimiter);
        outputText = only_allowed_chars(outputText);
        outputText = Trim(outputText);
	}
	return outputText;
};

function handleDisconnect(){
        logea("Lost connection to "+server+" on port "+port);
        delete _global.key;
        for (var b in timer_type) {
                timer_type[b] = "STOP";
                inicio_timer[b] = 0;
        }
        for (var a in _root) {
                if (typeof (_root[a]) == "movieclip") {
                        if (a.substring(0, 10) == "rectangulo") {
                                for (b in _root[a]) {
                                    if (_root[a][b]._name.substring(0, 7) == "casicol") {
										var numero = _root[a][b]._name.substring(7,1);
                            			_root[a][b].changeledcolor(3,_global.color[0],_global.color[1],numero);
									}
                                }
                        }
                }
                _root.log.gotoAndPlay(1);
        }
        _global.reconecta = 1;
}


function createCircle(tMC, r, x, y, color) {

	// constant used in calculation
	var A = Math.tan(22.5 * Math.PI/180);
	// variables for each of 8 segments
	var endx;
	var endy;
	var cx;
	var cy;

	color_normalizado = parseInt(color,16);
	with (tMC) {
        lineStyle(1,color_normalizado,100);
		beginFill(color_normalizado, 100);
		moveTo(x+r, y);
		for (var angle = 45; angle<=360; angle += 45) {
		   // endpoint
		   endx = r*Math.cos(angle*Math.PI/180);
		   endy = r*Math.sin(angle*Math.PI/180);
	 	  // control:
		   // (angle-90 is used to give the correct sign)
		   cx =endx + r* A *Math.cos((angle-90)*Math.PI/180);
		   cy =endy + r* A *Math.sin((angle-90)*Math.PI/180);
		   curveTo(cx+x, cy+y, endx+x, endy+y);
		}
		endFill();
	}
}

function createSquare(tMC, tW, tH, lW, lC, fC, f2C, cRad) {
	tW -= lW;
	tH -= lW;
	with (tMC) {
		var zerox = 0;
		var zeroy = (tH/2)*-1;
		lineStyle(lW, parseInt(lC, 16), 50);
		moveTo(cRad+lW+zerox, zeroy);
		colors = [parseInt(fC, 16), parseInt(f2C, 16)];
		alphas = [100, 100];
		ratios = [0, 0xFF];
		matrix = {a:200, b:0, c:0, d:0, e:200, f:0, g:200, h:200, i:1};
		matrix = {matrixType:"box", x:1, y:1, w:tW, h:tH, r:(45/180)*Math.PI};
		if(fC != "transparent") {
		beginGradientFill("linear", colors, alphas, ratios, matrix);
		}
		// 
		lineTo(cRad+lW+zerox, zeroy);
		curveTo(lW+zerox, zeroy, lW+zerox, cRad+zeroy);
		lineTo(lW+zerox, tH-cRad+zeroy);
		curveTo(lW+zerox, tH+zeroy, cRad+lW+zerox, tH+zeroy);
		lineTo(tW-cRad+zerox, tH+zeroy);
		// 
		curveTo(tW+zerox, tH+zeroy, tW+zerox, tH-cRad+zeroy);
		lineTo(tW+zerox, cRad+zeroy);
		curveTo(tW+zerox, zeroy, tW-cRad+zerox, zeroy);
		lineTo(cRad+lW+zerox, zeroy);
		endFill();
	}
};


dibuja = function () {

    _root.preload._visible=false;
	contador = 1;
	depth = -10000;
	cantidad_botones = 1;

	// Draw rectangles bellow buttons
	var a=-1;
	while (++a <= vr.total_rectangles) {
		v = eval("vr.rect_"+a);
		if (v != undefined) {
			var datos = v.split(",");
			var rect_x = Number(datos[0]);
			var rect_y = Number(datos[1]);
			var rect_width =  Number(datos[2]);
			var rect_height = Number(datos[3]);
			var line_width = Number(datos[4]);
			var line_color = datos[5];
			var fade_color1 = datos[6];
			var fade_color2 = datos[7];
			var rnd_border = Number(datos[8]);
			var alpha = Number(datos[9]);
			offsetboton = (rect_height/2);
			if(datos[10]=="bottom") {
				rect_y += offsetboton;
				cusl = createEmptyMovieClip("dibujo"+a, depth);
				createSquare(cusl, rect_width, rect_height, line_width, line_color, fade_color1, fade_color2, rnd_border);
				cusl._x = rect_x;
				cusl._y = rect_y;
				cusl._visible = true;
				cusl._alpha = alpha;
				cusl.useHandCursor = false;
				depth++;
			}
		}
	}


	var a=0;
	while (++a <= _root.cuantas_columnas) {
		depth++;
		var b=0;
		while (++b <= _root.cuantas_filas) {
			var coordenada_y = (b*(alto_boton+separacion))-alto_boton+30;
			var coordenada_x = (a*(ancho_boton+separacion))-ancho_boton;
			var coordenada_x = coordenada_x-separacion;
			var offsetboton = (alto_boton/2);
			var v = eval("vr.texto"+contador);

			var lurl    = eval("vr.url"+contador);
			lurl    = base64_decode(lurl);

			var lalarm  = eval("vr.alarm"+contador);
			if(lalarm != null) {
				lalarm = base64_decode(lalarm);
				
				var partes = lalarm.split("^");
				segundos_alarma = partes[0];
				url_alarma = partes[1];
				target_alarma = partes[2];

				timeralarm[contador] = partes[0];
				urlalarm[contador]   = partes[1];
				targetalarm[contador] = partes[2];
				

				logea("alarma "+contador+" = "+lalarm);
			}

			if(lurl != null) {
			logea("url "+lurl);
			}
			var ltarget = eval("vr.target"+contador);
			if (v != undefined) {
				// El boton esta definido en la configuracion, lo muestro

				if(lurl != "0" && lurl != null) {
					fmtLabel.url = lurl;
				} else {
					fmtLabel.url = undefined;
				}
				if(ltarget != "0") {
					fmtLabel.target = ltarget;
				} else {
					fmtLabel.target = undefined;
				}

				// Guardo el label original en un array
			    _global.labels[contador] = v;	
				var ima = eval("vr.bg"+contador);

				 cuel = createEmptyMovieClip("resaltado"+contador, getNextHighestDepth());
				if(_root["nodraw_"+contador] == undefined || ima != undefined) {
					createSquare(cuel, ancho_boton+4, alto_boton+4, ancho_linea, color_linea, resaltado_color, resaltado_color, btn_round_border);
				}
				with(cuel) { 
					_x = coordenada_x-2;
					_y = coordenada_y+offsetboton;
					_visible = false;
					useHandCursor = false;
				}

				if(ima != undefined) {
					cauel = createEmptyMovieClip("resaltadobg"+contador, getNextHighestDepth());
					with(cauel) {
						_x = coordenada_x;
						_y = coordenada_y;
						_visible = true;
						useHandCursor = false;
					}
					loadMovie(ima,cauel);
				}

				cual = createEmptyMovieClip("rectangulo"+contador, getNextHighestDepth());
				with(cual) {
					useHandCursor = false;
					//menu = myMenu;
					_visible = true;
					_x = coordenada_x;
					_y = coordenada_y+offsetboton;
				}

				led_margin_left = Number(vr.led_margin_left);
				led_margin_top = Number(vr.led_margin_top);
				if(_root["nodraw_"+contador] == undefined) {
				createSquare(cual, ancho_boton, alto_boton, ancho_linea, color_linea, boton1_fade, boton2_fade, btn_round_border);
				}


       			var lTextExtent = fmtClid.getTextExtent ( "XXXXXXXXXXXXXXXXXXXX" );
        	    var textWidth = lTextExtent.textFieldWidth + 10;
        	    var textHeight = lTextExtent.textFieldHeight + 2;
				cual.createTextField("statusprint"+contador, depth, clid_margin_left, clid_margin_top-offsetboton, 1, 1);
				depth++;
				with (cual["statusprint"+contador]) {
					selectable = false;
					setNewTextFormat(fmtClid);
					text = "000000000000000000000000000000000000";
					border = false;
					_width = vr.btn_width - clid_margin_left * 2;
					_width = textWidth;
					_height = textHeight + 5;
					text = "";
					if(use_embed_fonts==1) {
						embedFonts = true;
					}
				}

				cual.createTextField("timer"+contador, depth, timer_margin_left, timer_margin_top-offsetboton, 1, 1);
				depth++;
				cual["timer"+contador].selectable = false;
				cual["timer"+contador].border = false;
				cual["timer"+contador].setNewTextFormat(fmtTimer);
				cual["timer"+contador].text = "00:00:00";
				cual["timer"+contador]._width = cual["timer"+contador].textWidth+15;
				cual["timer"+contador]._height = cual["timer"+contador].textHeight+15;
				cual["timer"+contador].text = "";
				cual["timer"+contador].tabIndex = contador;
				if(use_embed_fonts==1) {
					cual["timer"+contador].embedFonts = true;
				}

       			var lTextExtent = fmtLabel.getTextExtent ( v );
        	    var lWidth  = lTextExtent.textFieldWidth + _global.label_extent_x;
        	    var lHeight = lTextExtent.textFieldHeight + _global.label_extent_y;

				cual.createTextField("textobg", depth, label_margin_left, label_margin_top-offsetboton, 1, 1);
				with (cual["textobg"]) {
					setNewTextFormat(fmtLabel);
					_width = vr.btn_width - label_margin_left * 2;
					_height = lHeight;
				}
				depth++;

				if (label_shadow == 1) {
					cual.createTextField("textosh", depth, label_margin_left+1, label_margin_top+1-offsetboton, 1, 1);
					with (cual["textosh"]) {
						//setNewTextFormat(fmtLabelsh);
						_width  = lWidth; 
						_height = lHeight; 
						selectable = false;
						border = showborders;
						multiline = true;
						wordWrap = true;
						if(use_embed_fonts==1) {
							embedFonts = true;
					    	htmlText = v;
						    html = true;
						} else {
							text = v;
						}
					}
					cual["textosh"].setTextFormat(fmtLabelsh);
					depth++;
				}

				cual.createTextField("textoprint", depth, label_margin_left, label_margin_top-offsetboton, 1, 1);
				with (cual["textoprint"]) {
				    setNewTextFormat(fmtLabel);
					_width  = lWidth; 
					_height = lHeight; 
					border = showborders;
					selectable = false;
					multiline = true;
					wordWrap = true;
					if(use_embed_fonts==1) {
						embedFonts = true;
					    htmlText = v;
					    html = true;
					} else {
						text = v;
					}
				}
				cual["textoprint"].setTextFormat(fmtLabel);
				depth++;

				casilli = cual.attachMovie("ledsombra","casish"+contador, depth,  {_x:led_margin_left, _y:led_margin_top-offsetboton+1});
				casilli._xscale = led_scale;
				casilli._yscale = led_scale;
				depth++;
				casilli = cual.attachMovie("ledcolor","casicol"+contador, depth, {_x:led_margin_left, _y:led_margin_top-offsetboton+1});
				casilli._xscale = led_scale;
				casilli._yscale = led_scale;
				_global.colorlibre[contador]=_global.color[0];
				casilli.changeledcolor(0,_global.color[0],_global.color[1],contador);
				depth++;
				casilli = cual.attachMovie("ledbrillo","casilla"+contador, depth, {_x:led_margin_left, _y:led_margin_top-offsetboton+1});
				casilli._xscale = led_scale;
				casilli._yscale = led_scale;

				depth++;

				circle = cual.createEmptyMovieClip("circle"+contador, depth);
				createCircle(circle,11,0,0,'0x00ff00');
				circle._xscale = arrow_scale;
				circle._yscale = arrow_scale;
				circle._x = arrow_margin_left;
				circle._y = arrow_margin_top - offsetboton + 1;

				depth++;

				flechi = cual.attachMovie("arrow", "flecha"+contador, depth, {_x:arrow_margin_left, _y:arrow_margin_top-offsetboton+1});
				depth++;
//				flechi._visible = false; 
				flechi.gotoAndStop(3);
				flechi._xscale = arrow_scale;
				flechi._yscale = arrow_scale;
				flechi.onRelease = function() {
					doubleClick(this);
				};

                flechi.onEnterFrame = function() {
                    if(this.hitTest(clip_arrastrado)) {
                        this._xscale = arrow_scale * 1.5;
                        this._yscale = arrow_scale * 1.5;
                        this.hitted = 1;
                        _global.flechahit = this;
                    } else {
                        if(this.hitted == 1) {
                            this.hitted = 0;
                            _global.flechahit = undefined;
                        }
                    }
                    if(this.hitted == 0) {
                        this._xscale = arrow_scale;
                        this._yscale = arrow_scale;
                        this.hitted = -1;
                    }
                };



				
//				createCircle(cual,12*(arrow_scale/100),arrow_margin_left,arrow_margin_top-offsetboton+1,'0xff0000');
//				var tama = (arrow_scale * 6 / 100) * 2;
//				createCircle(_root["rectangulo"+contador]["flecha"+contador],tama,0,0,'0xff0000');



				w = Number(eval("vr.icono"+contador));
				if (w<0) {
					w = 1;
				}
				top = Number(eval("vr.icon"+w+"_margin_top"));
				left = Number(eval("vr.icon"+w+"_margin_left"));
				escala = Number(eval("vr.icon"+w+"_scale"));
				telef = cual.attachMovie("telefono"+w, "tele"+contador, depth, {_x:ancho_boton+left, _y:top-offsetboton});
				telef.gotoAndStop(1);
				depth++;
				sobrec = cual.attachMovie("sobre", "sobrecito"+contador, depth, {_x:ancho_boton+mail_margin_left, _y:mail_margin_top-offsetboton});
				depth++;
				sobrec._xscale = mail_scale;
				sobrec._yscale = mail_scale;
				sobrec._visible = false;
				sobrec._alpha = _global.nomailalpha;

				sobrec.onRollOut = function() {
					this._xscale = mail_scale;
					this._yscale = mail_scale;
				};

				sobrec.onEnterFrame = function() {
					if(this.hitTest(clip_arrastrado)) {
						this._xscale = mail_scale * 1.5;
						this._yscale = mail_scale * 1.5;
						this.hitted = 1;
						_global.sobrehit = this;
					} else {
						if(this.hitted == 1) {
							this.hitted = 0;
							_global.sobrehit = undefined;
						}
					}
					if(this.hitted == 0) {
						this._xscale = mail_scale;
						this._yscale = mail_scale;
						this.hitted = -1;
					}
				};

				sobrec.onRollOver = function() {
				//	this._xscale = mail_scale * 1.5;
				//	this._yscale = mail_scale * 1.5;
					var origen = ExtraeNumeroClip(this);
					makeStatusMail(origen);
				};
				sobrec.onRelease = function() {
					doubleClick(this);
				};
				telef.onPress = function() {
					dragClip(this);
				};
				casilli.onRelease = function() {
					doubleClick(this);
				};
				telef._xscale = escala;
				telef._yscale = escala;

				inicio_timer[cantidad_botones] = 0;
				timer_type[contador] = "STOP";
				cantidad_botones++;
				ultimo = contador;
			} 
			contador++;
		}
	}

	// Draw rectangles over buttons
	var a=0;
	while (++a <= vr.total_rectangles) {
		v = eval("vr.rect_"+a);
		if (v != undefined) {
			var datos = v.split(",");
			var rect_x = Number(datos[0]);
			var rect_y = Number(datos[1]);
			var rect_width =  Number(datos[2]);
			var rect_height = Number(datos[3]);
			var line_width = Number(datos[4]);
			var line_color = datos[5];
			var fade_color1 = datos[6];
			var fade_color2 = datos[7];
			var rnd_border = Number(datos[8]);
			var alpha = Number(datos[9]);
			offsetboton = (rect_height/2);
			if(datos[10]=="top") {
				rect_y += offsetboton;
				cusl = createEmptyMovieClip("dibujo"+a, getNextHighestDepth());
				createSquare(cusl, rect_width, rect_height, line_width, line_color, fade_color1, fade_color2, rnd_border);
				cusl._x = rect_x;
				cusl._y = rect_y;
				cusl._visible = true;
				cusl._alpha = alpha;
				cusl.useHandCursor = false;
			}
		}
	}

	// Draw IMAGES
     
	var a3=0;
	while (++a3 <= vr.total_images) {
		v3 = eval("vr.image_"+a3);
		
		if (v3 != undefined) {
        	ima = eval("image"+a3);
			var datos3 = v3.split(",");

			var image_x = Number(datos3[0]);
			var image_y = Number(datos3[1]);
			var image_src = datos3[2];
			var image_url = datos3[3];
			var image_target = datos3[4];
			logea("coord x "+image_x);
			logea("coord y "+image_y);
			logea("image src "+image_src);


			barin = createEmptyMovieClip("image"+a3,getNextHighestDepth());
			loadMovie(image_src, "image"+a3);
			barin._x = image_x;
			barin._y = image_y;

		}

	}
    



	// Draw TEXT LEGENDS
	var a=0;
	while (++a <= vr.total_legends) {
		v = eval("vr.legend_"+a);
		if (v != undefined) {
			var datos = v.split(",");
			var legend_x = Number(datos[0]);
			var legend_y = Number(datos[1]);
			var legend_fontsize = Number(datos[3]);
			var legend_fontfamily = datos[4];
			var legend_fontcolor = parseInt(datos[5], 16);
			var legend_embedfonts = Number(datos[6]);
			var legend_no_base64 = Number(datos[7]);
			var legend_url = datos[8];
			var legend_target = datos[9];

			if(legend_no_base64==0) {
			    var legend_text = base64_decode(datos[2]);
			} else {
			    var legend_text = datos[2];
			}

    		var fmtLege = new TextFormat();
    		fmtLege.size = legend_fontsize;
			fmtLege.color = legend_fontcolor;

			if(legend_embedfonts == 1) {
    			fmtLege.font = "$fuente_nombre";
			} else {
    			fmtLege.font = legend_fontfamily;
			}

			if(legend_url != "no") {
				fmtLege.url = legend_url;
				fmtLege.underline = true;
				logea("legend url "+fmtLege.url);
			}

			if(legend_target != "NONTARFOP") {
				fmtLege.target = legend_target;
			}

    		createTextField("lege"+a, getNextHighestDepth(), legend_x, legend_y, 10, 10);
    		_root["lege"+a].setNewTextFormat(fmtLege);
			if(legend_embedfonts == 1) {
    			_root["lege"+a].embedFonts = true;
			} else {
    			_root["lege"+a].embedFonts = false;
			}
    		_root["lege"+a].htmlText = legend_text;
			_root["lege"+a].html = true;
			_root["lege"+a].border = false;
			_root["lege"+a].selectable = false;
			_root["lege"+a].multiline = true;
			_root["lege"+a].wordWrap = true;

        	var lTextExtent = fmtLege.getTextExtent ( legend_text );
        	var lWidth  = lTextExtent.textFieldWidth + 40;
        	var lHeight = lTextExtent.textFieldHeight + 40;

			_root["lege"+a]._width  = lWidth;
			_root["lege"+a]._height = lHeight;
    		_root["lege"+a].setTextFormat(fmtLege);
		}
	}
	ultimo++;
	attachMovie("telefono1", "tele"+ultimo, getNextHighestDepth(), {_x:1, _y:1, _visible:false});
	_global.masalto = ultimo;

	_root.onMouseMove = function() {
		if(_global.nohighlight == 1) {
			return;
		}
		var columna = int((_xmouse+ancho_boton+separacion)/(ancho_boton+separacion));
		var fila = Math.floor((_ymouse-30-separacion)/(alto_boton+separacion))+1;
		var boton = 1;
		if (columna<1) {
			columna = 1;
			boton = -1;
		} else if (columna>cuantas_columnas) {
			columna = cuantas_columnas;
			boton = -1;
		}
		if (fila>cuantas_filas) {
			fila = cuantas_filas;
			boton = -1;
		} else if (fila<1) {
			fila = 1;
			boton = -1;
		}
		if(boton!=-1) {
		boton = ((columna*cuantas_filas)-cuantas_filas)+fila;
			if (boton!=_global.rectanguloprendido) {
				var a=0;
				while (++a < _global.otrosprendidos.length) {
					var prendeme = eval("resaltado"+_global.otrosprendidos[a]);
					prendeme._visible = false;	
				}

				//var myresa = eval("resaltado"+boton);
				//var myapaga = eval("resaltado"+_global.rectanguloprendido);
				if(_global.rectanguloprendido != _global.restrict) {
					//myapaga._visible = false;
					_root["resaltado"+_global.rectanguloprendido]._visible = false;
				}
				//myresa._visible = true;
				_root["resaltado"+boton]._visible = true;
				_global.rectanguloprendido = boton;
				makeStatus(boton);

				var botresa = linkeado[boton].split(",");
				for (b in botresa) {
					//var prendeme = eval("resaltado"+botresa[b]);
					//prendeme._visible = true;	
					_root["resaltado"+botresa[b]]._visible = true;
					_global.otrosprendidos.push(botresa[b]);
				}
			}
		} else {
			//var myapaga = eval("resaltado"+_global.rectanguloprendido);
			//myapaga._visible = false;
			_root["resaltado"+_global.rectanguloprendido]._visible = false;
			for (b in _global.otrosprendidos) {
				//var prendeme = eval("resaltado"+_global.otrosprendidos[b]);
				if(_global.otrosprendidos[b]!=_global.restrcit) {
					//prendeme._visible = false;	
					_root["resaltado"+_global.otrosprendidos[b]]._visible = false;
				}
			}
		}
	};
 _root.detail.swapDepths(getNextHighestDepth());
 _root.superdetails.swapDepths(getNextHighestDepth());
 _root.log.swapDepths(getNextHighestDepth());
 _root.codebox.swapDepths(getNextHighestDepth());

 _root.selectbox1.swapDepths(getNextHighestDepth());
 if(vr.totaltimes > 0) {
	genera_selecttimeout();
	_root.selectbox1._visible = true;
 } else {
	_root.selectbox1._visible = false;
 }

if(defined(_root.margintop))
{
	_root._y+=_root.margintop;
}

if(defined(_root.marginleft))
{
	_root._x+=_root.marginleft;
}

};


Inicia_Variables = function () {
    lastclick=0;
	doubleclickduration=300;
	_global.swfversion="0.30";
	_global.frames = 0;
	_global.step=2;
	_global.statusline="";
	_global.server = vr.server;
	_global.port = vr.port;
	_global.clid_centered = Number(vr.clid_centered);
	_global.shakepixels = Number(vr.shake_pixels);
	_global.nomailalpha = Number(vr.nomail_alpha);
    if (Number(vr.show_borders)==1) {
		_global.showborders = true;
    } else {
		_global.showborders = false;
	}
	_root.log._visible = false;
	_root.log._alpha = 100;
	_root.codebox._visible = false;
	_root.codebox._alpha = 100;
	_root.detail._visible = false;
	_root.superdetails._visible = false;
	_root.superdetails.tab1.gotoAndStop(1);
	_root.superdetails.tab2.gotoAndStop(2);
	_root.superdetails._alpha = 90;
	_global.logwindow = 1;
	_global.margenbar = 2;
	_global.chanvars = new Array();
	_global.loglines = new Array();
	_global.texto_tip = new Array();
	_global.texto_mail = new Array();
	_global.timeralarm = new Array();
	_global.urlalarm = new Array();
	_global.targetalarm = new Array();
	_global.st_originclid = new Array();
	_global.st_destinationclid = new Array();
    _global.ipboton = new Array();
	_global.st_direction = new Array();
	_global.queuemember = new Object();
	_global.superdetailstexttab1 = "";
	_global.superdetailstexttab2 = "";
	_global.st_duration = new Array();
	_global.meetmemember = new Array();
	_global.meetmeroom = new Array();
	_global.meetmemute = new Array();
	_global.linkeado = new Array();
	_global.authorized = false;
	_global.wait5seconds = 1;
	_global.rectanguloprendido = 1;
	_global.otrosprendidos = new Array();
	_global.colorlibre = new Array();
	_global.labels = new Array();
	_global.clidnumber = new Array();
	_global.clidname = new Array();
	_global.enable_crypto = Number(vr.enable_crypto);

	_global.label_extent_x = Number(vr.label_extent_x);
	_global.label_extent_y = Number(vr.label_extent_y);
	if (isNaN(_global.label_extent_x)) {
		_global.label_extent_x = 10;
    }
	if (isNaN(_global.label_extent_y)) {
		_global.label_extent_y = 5;
    }

    _global.enable_label_background = Number(vr.enable_label_background);
	_global.restart = Number(vr.restart);
	_global.nosecurity = vr.nosecurity;
    _global.valorchangeledcolor = new Array();
	if(_global.nosecurity == 1) {
		_global.claveingresada="";
	}
	_global.claveingresada = LocalLoad("auth","clave");
	_root.codebox.claveform.text = _global.claveingresada;
	_global.timeout_value = 0;
	if (isNaN(_global.enable_crypto)) {
		_global.enable_crypto=0;
	} else {
		if(_global.enable_crypto != 0) {
			_global.enable_crypto = 1;
		}
	}

	_global.led_color = Number(vr.led_color);
	if (_global.led_color>1 || _global.led_color<0) {
		_global.led_color = 0;
	}
	// ancho_pantalla = System.capabilities.screenResolutionX;
	// alto_pantalla = System.capabilities.screenResolutionY;
	ancho_pantalla_real = $stage_width;
	alto_pantalla_real = $stage_height;
	ancho_boton = Number(_root.vr.btn_width);
	alto_boton = Number(_root.vr.btn_height);

	if (ancho_boton<=0) {
		ancho_boton = 200;
	}
	if (alto_boton<=0) {
		alto_boton = 80;
	}
	separacion = Number(_root.vr.btn_padding);
	ancho_linea = Number(_root.vr.btn_line_width);
	color_linea = _root.vr.btn_line_color;
	boton1_fade = _root.vr.btn_fadecolor_1;
	boton2_fade = _root.vr.btn_fadecolor_2;
	ledcolor_busy = _root.vr.ledcolor_busy;
	ledcolor_ready = _root.vr.ledcolor_ready;
	ledcolor_agent = _root.vr.ledcolor_agent;
	ledcolor_paused = _root.vr.ledcolor_paused;
	_global.resaltado_color = _root.vr.btn_highlight_color;

	if(ledcolor_busy==undefined) {
		ledcolor_busy="0xC00000";
	}
	if(ledcolor_ready==undefined) {
		ledcolor_ready="0x00A000";
	}
	if(ledcolor_agent==undefined) {
		ledcolor_agent="0xC0A000";
	}
	if(_global.resaltado_color==undefined) {
		_global.resaltado_color="FF0000";
	}

    // Fills 30 elements in loglines (for debug window)
	for (i=0; i<30; i++) {
		_global.loglines.push("");
	}

	_global.color = new Array();
	_global.color[0] = ledcolor_ready;
	_global.color[1] = ledcolor_busy;
	_global.color[2] = ledcolor_agent;
	btn_round_border = Number(_root.vr.btn_round_border);
	label_margin_top = Number(_root.vr.label_margin_top);
	label_margin_left = Number(_root.vr.label_margin_left);
	clid_margin_top = Number(_root.vr.clid_margin_top);
	clid_margin_left = Number(_root.vr.clid_margin_left);
	timer_margin_top = Number(_root.vr.timer_margin_top);
	timer_margin_left = Number(_root.vr.timer_margin_left);
	if(_root.vr.dimm_noregister_by == undefined) {
		dimm_noregister = 20;
	} else {
		dimm_noregister = Number(_root.vr.dimm_noregister_by);
	}
	if(_root.vr.dimm_lagged_by == undefined) {
		dimm_lagged = 60;
	} else {
		dimm_lagged = Number(_root.vr.dimm_lagged_by);
	}
	if(_root.vr.label_font_color == undefined) {
         _root.vr.label_font_color='000000';
    }
	if(_root.vr.label_shadow_color == undefined) {
         _root.vr.label_shadow_color='dddddd';
    }
	label_font_color = parseInt(_root.vr.label_font_color, 16);
	label_shadow_color = parseInt(_root.vr.label_shadow_color, 16);
	clid_font_color = parseInt(_root.vr.clid_font_color, 16);
	timer_font_color = parseInt(_root.vr.timer_font_color, 16);
	label_font_size = Number(_root.vr.label_font_size);
	arrow_margin_left = Number(vr.arrow_margin_left);
	arrow_margin_top = Number(vr.arrow_margin_top);
	led_scale = Number(_root.vr.led_scale);
	arrow_scale = Number(_root.vr.arrow_scale);
	// phone_scale = Number(phone_scale);
	mail_margin_left = Number(_root.vr.mail_margin_left);
	mail_margin_top = Number(_root.vr.mail_margin_top);
	mail_scale = Number(_root.vr.mail_scale);
	show_security_code = Number(_root.vr.show_security_code);
	show_clid_info = Number(_root.vr.show_clid_info);
	show_btn_help = Number(_root.vr.show_btn_help);
	show_btn_debug = Number(_root.vr.show_btn_debug);
	show_btn_reload = Number(_root.vr.show_btn_reload);
	show_status = Number(_root.vr.show_status);
	use_embed_fonts = Number(_root.vr.use_embed_fonts);
	enable_animation = Number(_root.vr.enable_animation);
	label_shadow = Number(_root.vr.label_shadow);
	_level0.detail.duration_label = _root.vr.detail_duration;
	_level0.detail.title = _root.vr.detail_title;
	_level0.codebox.title = _root.vr.security_code_title;
	_level0.superdetails.titlefs2 = _root.vr.tab_call_text;
	_level0.superdetails.titlefs3 = _root.vr.tab_queue_text;
    _level0.log.title = _root.vr.debug_window_title;
	orden_barra = [["security", vr.show_security_code], ["clid", vr.show_clid_info], ["status", vr.show_status], ["help", vr.show_btn_help], ["debug", vr.show_btn_debug], ["reload", vr.show_btn_reload]];
	orden_barra.sort(ordenaArray);
	// cuantas_filas = math.floor((alto_pantalla_real-30)/(alto_boton+separacion));
	cuantas_filas = (alto_pantalla_real-30)/(alto_boton+separacion);
	cuantas_filas = Math.floor(_root.cuantas_filas);
	// cuantas_columnas = math.floor((ancho_pantalla_real+separacion)/(ancho_boton+separacion));
	cuantas_columnas = (ancho_pantalla_real+separacion)/(ancho_boton+separacion);
	cuantas_columnas = Math.floor(Number(_root.cuantas_columnas));

	max_en_pantalla = cuantas_columnas * cuantas_filas;

	if(_root.vr.highestpos == undefined) {
		_root.vr.highestpos = 2;
	}
    _global.highpos = Number(_root.vr.highestpos);
	cuantas_columnas = Math.ceil(_global.highpos / cuantas_filas);
	_global.max_scroll_left = 996 - ( cuantas_columnas * (ancho_boton+separacion)) + separacion;
	if(_global.highpos <= max_en_pantalla) {
		_global.scrolling = 0;
	} else {
		_global.scrolling = 1;
	}


	fmtLabel = new TextFormat();
	fmtLabel.size = vr.label_font_size;
	if(use_embed_fonts==1) {
		fmtLabel.font = "$fuente_nombre";
	} else {
		fmtLabel.font = vr.label_font_family;
	}
	fmtLabel.color = label_font_color;

	fmtLabelsh = new TextFormat();
	fmtLabelsh.color = label_shadow_color;
	fmtLabelsh.size = vr.label_font_size;
	if(use_embed_fonts==1) {
		fmtLabelsh.font = "$fuente_nombre";
	} else {
		fmtLabelsh.font = vr.label_font_family;
	}
	fmtLabelsh._alpha = 90;

	fmtClid = new TextFormat();
	fmtClid.size = vr.clid_font_size;
	fmtClid.color = clid_font_color;
	if(use_embed_fonts==1) {
		fmtClid.font = "$fuente_nombre";
	} else {
		fmtClid.font = vr.clid_font_family;
	}

	fmtTimer = new TextFormat();
	fmtTimer.size = vr.timer_font_size;
	fmtTimer.color = timer_font_color;
	if(use_embed_fonts==1) {
		fmtTimer.font = "$fuente_nombre";
	} else {
		fmtTimer.font = vr.timer_font_family;
	}

	_global.ancho_a_centrar = vr.btn_width - clid_margin_left * 2;

	timer_type = new Array();
	inicio_timer = new Array();
	/* Custom context menu is not working in MING
		var myMenu;
		myMenu = new ContextMenu();
		myMenu.customItems.push(new ContextMenuItem("Toggle DND", setDND));
		myMenu.hideBuiltInItems();
	*/


	var i=0;
	while (++i < orden_barra.length) {
//	for (i=0; i<orden_barra.length; i++) {
		if (orden_barra[i][1]>0) {
			if (orden_barra[i][0] == "security") {
				Barra_SecurityCode();
			}
			if (orden_barra[i][0] == "clid") {
				Barra_InfoText();
			}
			if (orden_barra[i][0] == "help") {
				Barra_BotonHelp();
			}
			if (orden_barra[i][0] == "debug") {
				Barra_BotonDebug();
			}
			if (orden_barra[i][0] == "reload") {
				Barra_BotonReload();
			}
			if (orden_barra[i][0] == "status") {
				Barra_Status();
			}
		}
	}



_global.opcionesTimeout = new Array();
_global.opcionesTimeoutSecs = new Array();
var a=0;
while (++a <= vr.totaltimes) {
//for (a=1; a<=vr.totaltimes; a++) {
	v = eval("vr.timeout_"+a);
		if (v != undefined) {
			var datos = v.split(",");
			_global.opcionesTimeoutSecs.push(Number(datos[0]));
			_global.opcionesTimeout.push(datos[1]);
		}

}

datosnod = vr.nodraw.split(',');
for (var elem in datosnod) {
	_root["nodraw_"+datosnod[elem]] = 1;
}
};


function ordenaArray(a, b) {
	return a[1]>b[1];
}


function SecurityCode_Locked() {
    if(show_security_code != 0) {
		var x=_root.claveinput._x;
		var mydepth = _level0.claveinput.getDepth();
		// _level0.claveinput.unloadMovie();
		mymc = attachMovie("boton_security", "claveinput", mydepth);
		mymc.gotoAndStop(1);
		mymc._x = x;
		mymc._y = 1;
		mymc.onPress = function() {
			_root.codebox._visible = true;
			Selection.setFocus(_root.codebox.claveform);
			_root.codebox.swapDepths(_root.log);
		};
		mymc.onRollOver = function() {
			_root.statusbar.status.text = "Open Security Code Input Box";
			mymc.gotoAndStop(2);
		};
		mymc.onRollOut = function() {
			_root.statusbar.status.text = _global.statusline;
			mymc.gotoAndStop(1);
		};
	}
}

function SecurityCode_Unlocked() {
    if(show_security_code != 0) {
		var x=_root.claveinput._x;
		var mydepth = _level0.claveinput.getDepth();
		// _level0.claveinput.unloadMovie();
		mymc = attachMovie("boton_security_unlock", "claveinput", mydepth);
		mymc._x = x;
		mymc._y = 1;
		mymc.onPress = function() {
			_root.codebox._visible = true;
			Selection.setFocus(_root.codebox.claveform);
			_root.codebox.swapDepths(_root.log);
		};
		mymc.onRollOver = function() {
			_level0.statusbar.status.text = "Open Security Code input box";
			mymc.gotoAndStop(2);
		};
		mymc.onRollOut = function() {
			_level0.statusbar.status.text = _global.statusline;
			mymc.gotoAndStop(1);
		};
	}
}

function Barra_SecurityCode() {
	attachMovie("boton_security_unlock", "claveinput", getNextHighestDepth());
	claveinput.gotoAndStop(1);
	claveinput._x = _global.margenbar;
	_global.margen_claveinput = _global.margenbar;
	claveinput._y = 1;
	claveinput._visible = true;
	claveinput.onPress = function() {
		_root.codebox._visible = true;
		Selection.setFocus(_root.codebox.claveform);
		_root.codebox.swapDepths(_root.log);
	};
	claveinput.onRollOver = function() {
		_level0.statusbar.status.text = vr.btn_security_text;
		claveinput.gotoAndStop(2);
	};
	claveinput.onRollOut = function() {
		_level0.statusbar.status.text = _global.statusline;
		claveinput.gotoAndStop(1);
	};
	_global.margenbar = _global.margenbar+claveinput._width;
}

function Barra_BotonReload() {
	attachMovie("boton_reload", "buttonreload", getNextHighestDepth());
	buttonreload.gotoAndStop(1);
	_global.margen_buttonreload = _global.margenbar;
	buttonreload._x = _global.margenbar;
	buttonreload._y = 1;
	buttonreload._visible = true;
	buttonreload.label.text = vr.btn_reload_label;
	buttonreload.onRelease = function() {
		_root.recarga();
	};
	buttonreload.onRollOver = function() {
		if(_global.restart == 1) {
			_level0.statusbar.status.text = vr.btn_restart_text;
		} else {
			_level0.statusbar.status.text = vr.btn_reload_text;
		}
		buttonreload.gotoAndStop(2);
	};
	buttonreload.onRollOut = function() {
		_level0.statusbar.status.text = _global.statusline;
		buttonreload.gotoAndStop(1);
	};
	_global.margenbar = _global.margenbar+buttonreload._width;
}


function Barra_BotonDebug() {
	attachMovie("boton_debug", "buttondebug", getNextHighestDepth()); 
	buttondebug.gotoAndStop(1);
	_global.margen_buttondebug = _global.margenbar;
	buttondebug._x = _global.margenbar;
	buttondebug._y = 1;
	buttondebug._visible = true;
	buttondebug.label.text = vr.btn_log_label;
	buttondebug.onPress = function() {
		MuestraLog();
	};
	buttondebug.onRollOver = function() {
		_level0.statusbar.status.text = vr.btn_debug_text + " (" +_global.swfversion + ")";
		buttondebug.gotoAndStop(2);
	};
	buttondebug.onRollOut = function() {
		_level0.statusbar.status.text = _global.statusline;
		buttondebug.gotoAndStop(1);
	};
	_global.margenbar = _global.margenbar+buttondebug._width;
}

function Barra_BotonHelp() {
	attachMovie("boton_ayuda", "buttonhelp", getNextHighestDepth()); 
	buttonhelp.gotoAndStop(1);
	_global.margen_buttonhelp = _global.margenbar;
	buttonhelp._x = _global.margenbar;
	buttonhelp._y = 1;
	buttonhelp._visible = true;
	buttonhelp.label.text = vr.btn_help_label;
	buttonhelp.onPress = function() {
		buttonhelp.gotoAndStop(2);
		url = "help_"+vr.lang+".html";
		winName = "fophelp";
		w = 400;
		h = 300;
		toolbar = 0;
		location = 0;
		directories = 0;
		status = 0;
		menubar = 0;
		scrollbars = 1;
		resizable = 0;
		getURL("javascript:var myWin1;if (!myWin1 || myWin1.closed){myWin1=window.open('"+url+"', '"+winName+"', '"+"width="+w+", height="+h+", toolbar="+toolbar+", location="+location+", directories="+directories+", status="+status+", menubar="+menubar+", scrollbars="+scrollbars+", resizable="+resizable+", top='+((screen.height/2)-("+h/2+"))+', left='+((screen.width/2)-("+w/2+"))+'"+"')} else{myWin1.focus();};void(0);",''); 
	};
	buttonhelp.onRollOver = function() {
		_level0.statusbar.status.text = vr.btn_help_text;
		buttonhelp.gotoAndStop(2);
	};
	buttonhelp.onRollOut = function() {
		_level0.statusbar.status.text = _global.statusline;
		buttonhelp.gotoAndStop(1);
	};
	_global.margenbar = _global.margenbar+buttonhelp._width;
}


function Barra_Status() {
	ancho = 0;
	var j=0;
	while (++j < orden_barra.length) {
//	for (j=0; j<orden_barra.length; j++) {
		if (orden_barra[j][1]>0) {
			if (orden_barra[j][0] == "security") {
				ancho = ancho+32.5;
			}
			if (orden_barra[j][0] == "clid") {
				ancho = ancho+272.5;
			}
			if (orden_barra[j][0] == "help") {
				ancho = ancho+32.5;
			}
			if (orden_barra[j][0] == "debug") {
				ancho = ancho+32.5;
			}
			if (orden_barra[j][0] == "reload") {
				ancho = ancho+32.5;
			}
		}
	}
	ancho_status = ancho_pantalla_real-(ancho+2);

	bar = createEmptyMovieClip("statusbar",30000);
	_global.margen_statusbar = _global.margenbar;
	bar._visible = true;
	bar._border = true;
	createSquare(_level0.statusbar, ancho_status, 30, 0, "0x999999", "0xcccccc", "0xcccccc", 0);
	bar._x = _global.margenbar;
	bar._y = 16;

    fmtStatus = new TextFormat();
    fmtStatus.size = 16;
	if(use_embed_fonts==1) {
	    fmtStatus.font = "$fuente_nombre";
	} else {
		fmtStatus.font = vr.label_font_family;
	}

	statusTextExtent = fmtStatus.getTextExtent ( "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX" );
    statusWidth  = statusTextExtent.textFieldWidth;
    statusHeight = statusTextExtent.textFieldHeight;
    bar.createTextField("status", getNextHighestDepth(), 3, 3, statusWidth, statusHeight);
    bar.status.setNewTextFormat(fmtStatus);
    bar.status.text = "Asterisk Flash Operator Panel";
    bar.status.border = false;
	bar.status._y = -10;
    bar.status.selectable = false;
	if(use_embed_fonts==1) {
	    bar.status.embedFonts = true;
	} 

}

function Barra_InfoText() {
    fmtInfo = new TextFormat();
    fmtInfo.size = 16;
	if(use_embed_fonts==1) {
	    fmtInfo.font = "$fuente_nombre";
	} else {
		fmtInfo.font = vr.label_font_family;
	}
	attachMovie("infotext", "infotext1", getNextHighestDepth());
	_global.margen_infotext1 = _global.margenbar;
	infotext1._x = _global.margenbar;
	infotext1._y = 1;
	infotext1._visible = true;
	infoTextExtent = fmtInfo.getTextExtent ( vr.clid_label );
    infoWidth  = infoTextExtent.textFieldWidth + 20;
    infoHeight = infoTextExtent.textFieldHeight;
    infotext1.createTextField("cclid", getNextHighestDepth(), 3, 3, infoWidth, infoHeight);
	infotext1.cclid.setNewTextFormat(fmtInfo);
	infotext1.cclid.text = vr.clid_label;
	infotext1.cclid.border = false;
	if(use_embed_fonts==1) {
	    infotext1.cclid.embedFonts = true;
	} 
    inputtext = "XXXXXXXXXXXXXXXXXXX";
	infoTextExtent = fmtInfo.getTextExtent ( inputtext );
    infoWidth  = infoTextExtent.textFieldWidth;
    infoHeight = infoTextExtent.textFieldHeight;
    infotext1.createTextField("clid_text", getNextHighestDepth()+1, 74, 3, 195, infoHeight);
	infotext1.clid_text.setNewTextFormat(fmtInfo);
	infotext1.clid_text.text = "";
	infotext1.clid_text.border = false;
	infotext1.clid_text.type = "input";
	if(use_embed_fonts==1) {
	    infotext1.clid_text.embedFonts = true;
	} 

	_global.margenbar = _global.margenbar+infotext1._width;
}

recarga = function () {
  	if(_global.restart == 1) {
		// Send command to restart Asterisk
		envia_comando("restart","1","1");
	} else {
		// Reloads FLASH client
		delete _global.key;
		var incontext = context;
		var inbutton = mybutton;
		var inrestrict = _global.restrict;
		var indial = dial;
		var innohighlight = nohighlight;
		for (var a in _root) {
			if (typeof (_root[a]) == "object") {
				removeMovieClip(_root[a]);
			}
			if (typeof (_root[a]) == "movieclip") {
				removeMovieClip(_root[a]);
			}
		}
		_global.context = incontext;
		_global.mybutton = inbutton;
		_global.restrict = inrestrict;
		_global.dial = indial;
		_global.nohighlight = inhighlight;
		stop();
		gotoAndPlay(1);
		}
};


Detiene_Peliculas = function () {	
	// Detiene todos las peliculas al inicio
 	selectbox1.gotoAndStop(1);
	for (a in _root) {
		if (typeof (a) == "movieclip") {
			_root[a].stop();
			if (a.substring(0, 10) == "rectangulo") {
				for (b in _root[a]) {
					if (typeof (_root[a][b]) == "movieclip") {
						_root[a][b].stop();
					}
					if(b.substring(0,7) == "casicol") {
						var numero = b.substring(7,8);
						_root[a][b].changeledcolor(0,_global.colorlibre[numero],_global.color[1],numero);
					}
				}
			}
		}
	}
  _root._y = _root.margintop;
  _root._x = _root.marginleft;
};

_root.onEnterFrame = function() {

		if(_global.scrolling == 1) {
		// _level0.statusbar.status.text = _xmouse+":"+_ymouse;
		// Nico SCROLL
		var limite_der = 986 + (_root._x * -1);
		var limite_der_top = 986 + (_root._x * -1) + 10;
		var limite_izq = 10 + (_root._x * -1);
		var limite_izq_top = 10 + (_root._x * -1) - 10;
		if(_xmouse > limite_der && _xmouse <= limite_der_top) {
			_root._x -= _global.step;
			claveinput._x += _global.step;
			infotext1._x += _global.step;
			_level0.statusbar._x += _global.step;
			buttonhelp._x += _global.step;
			buttondebug._x += _global.step;
			buttonreload._x += _global.step;
			optionselected._x += _global.step;
			selectbox1._x += _global.step;
			_root.fondo.mihijo._x += _global.step;
			_root.option0._x += _global.step;
			_root.option1._x += _global.step;
			_root.option2._x += _global.step;
			_root.option3._x += _global.step;
			_root.option4._x += _global.step;
			_root.codebox._x += _global.step;
			_root.superdetails._x += _global.step;
			_global.step+=2;
		} else if (_xmouse < limite_izq && _xmouse>= limite_izq_top) {
			_root._x += _global.step;
			claveinput._x -= _global.step;
			infotext1._x -= _global.step;
			_level0.statusbar._x -= _global.step;
			buttonhelp._x -= _global.step;
			buttondebug._x -= _global.step;
			buttonreload._x -= _global.step;
			optionselected._x -= _global.step;
			selectbox1._x -= _global.step;
			_root.fondo.mihijo._x -= _global.step;
			_root.option0._x -= _global.step;
			_root.option1._x -= _global.step;
			_root.option2._x -= _global.step;
			_root.option3._x -= _global.step;
			_root.option4._x -= _global.step;
			_root.codebox._x -= _global.step;
			_root.superdetails._x -= _global.step;
			_global.step+=2;
		} else {
			_global.step=2;
		}
		if(_root._x>0) {
			_root._x = 0; 
			buttondebug._x  = _global.margen_buttondebug;
			buttonreload._x = _global.margen_buttonreload;
			buttonhelp._x = _global.margen_buttonhelp;
			claveinput._x   = _global.margen_claveinput;
			infotext1._x   = _global.margen_infotext1;
			_level0.statusbar._x   = _global.margen_statusbar;
			optionselected._x   = 800;
			selectbox1._x   = 800;
			_root.option0._x = 800;
			_root.option1._x = 800;
			_root.option2._x = 800;
			_root.option3._x = 800;
			_root.option4._x = 800;

			_root.codebox._x = 240;
			_root.superdetails._x = 490;
			_root.fondo.mihijo._x = 1;
		} else if(_root._x < _global.max_scroll_left) {
			_root._x = _global.max_scroll_left;
			buttondebug._x  = _global.margen_buttondebug + ( _global.max_scroll_left * -1);
			buttonreload._x = _global.margen_buttonreload + ( _global.max_scroll_left * -1);
			buttonhelp._x = _global.margen_buttonhelp + (_global.max_scroll_left * -1);
			claveinput._x   = _global.margen_claveinput + (_global.max_scroll_left * -1);
			infotext1._x   = _global.margen_infotext1 + (_global.max_scroll_left * -1);
			_level0.statusbar._x   = _global.margen_statusbar + (_global.max_scroll_left * -1);
			optionselected._x   = 800 + (_global.max_scroll_left * -1);
			selectbox1._x   = 800 + (_global.max_scroll_left * -1);
			_root.option0._x = 800 + (_global.max_scroll_left * -1);
			_root.option1._x = 800 + (_global.max_scroll_left * -1);
			_root.option2._x = 800 + (_global.max_scroll_left * -1);
			_root.option3._x = 800 + (_global.max_scroll_left * -1);
			_root.option4._x = 800 + (_global.max_scroll_left * -1);
//	 		for (var a=0; a<5; a++) {
//			 	var v = eval("_root.option"+a);
//				v._x = 800 + (_global.max_scroll_left * -1);
//			}
			_root.codebox._x = 240 + (_global.max_scroll_left * -1);
			_root.superdetails._x = 490 + (_global.max_scroll_left * -1);
			_root.fondo.mihijo._x = 1 + (_global.max_scroll_left * -1);
		}
		}

  if (lastclick>0) {
    if ((getTimer()-lastclick)>doubleclickduration) {
      lastclick = 0;
      logea ("single click "+lastclip);
	  var clip=lastclip;
	  var numeroclip = ExtraeNumeroClip(clip);

		/* new_way op_actions 
  		if (_global.authorized == true) {
		    envia_comando("singleclick",clip,"");
		} else {
	   	    _root.codebox._visible = true;
		    Selection.setFocus(_root.codebox.claveform);
		    _root.codebox.swapDepths(_root.log);
		    return;
		}
		*/
		// Un solo click
		if (clip._name.substring(0, 7) != "casilla") {
			if (_global.authorized == true) {
				if (_root.context.length>0) {
				     boton_numero_con_contexto = numeroclip+"@"+context;
				} else {
				     boton_numero_con_contexto = numeroclip;
				}
				if (_global.meetmemember[numeroclip]>0) {
					var statusclid = eval("rectangulo"+numeroclip+".statusprint"+numeroclip);
					if (_global.meetmemute[numeroclip]>0) {
						logea("envio meetmemute");
						envia_comando("meetmemute"+boton_numero_con_contexto+"-", _global.meetmemember[numeroclip], _global.meetmeroom[numeroclip]);
						statusclid.text = "Conference "+_global.meetmeroom[numeroclip]+" muted";
						_global.meetmemute[numeroclip]=0;
					} else {
						logea("envio meetmeunmute");
						envia_comando("meetmeunmute"+boton_numero_con_contexto+"-", _global.meetmemember[numeroclip], _global.meetmeroom[numeroclip]);
						statusclid.text = "Conference "+_global.meetmeroom[numeroclip];
						_global.meetmemute[numeroclip]=1;
					}
				} else {
					logea("no es meetmemember, empiezo record");
					envia_comando("startmonitor",numeroclip,numeroclip);
				} 
			} else {
				// Not authorized
				envia_comando("bogus", 0, 0);
				logea("no esta autorizado");				
			}
		} else {
			logea("single click en led no hace nada "+clip._name);
		}
	}
  }
};

Timers = function () {

	
	if (_global.reconecta == 1) {
		delete setInterval;
		delete _global.key;
		if (_global.wait5seconds>9) {
			_global.wait5seconds = 0;
			recarga();
		} else {
			_global.wait5seconds += 1;
		}
		return;
	}
	var floor = Math.floor;
	for (var a in timer_type) {
		var v = eval("texto"+a);
		if (v != "") {
			if (timer_type[a] == "UP" || timer_type[a] == "IDLE") {
				// calculate values
				var elapsedTime = getTimer()-inicio_timer[a];

				// ALARMA TIMER
				var segundos = floor( elapsedTime / 1000);
				if( segundos == timeralarm[a]) {
					popup_window(urlalarm[a],targetalarm[a]);
					logea(segundos);
				}
				// hours
				var elapsedHours = floor(elapsedTime/3600000);
				var remaining = elapsedTime-(elapsedHours*3600000);
				// minutes
				var elapsedM = floor(remaining/60000);
				remaining = remaining-(elapsedM*60000);
				// seconds
				var elapsedS = floor(remaining/1000);

				if (elapsedHours<0) {
					elapsedHours = Math.abs(elapsedHours);
				}
				if (elapsedM<0) {
					elapsedM = Math.abs(elapsedM);
				}
				if (elapsedS<0) {
					elapsedS = Math.abs(elapsedS);
				}

				if (elapsedHours<10) {
					var hours = "0"+elapsedHours.toString();
				} else {
					var hours = elapsedHours.toString();
				}
				if (elapsedM<10) {
					var minutes = "0"+elapsedM.toString();
				} else {
					var minutes = elapsedM.toString();
				}
				if (elapsedS<10) {
					var seconds = "0"+elapsedS.toString();
				} else {
					var seconds = elapsedS.toString();
				}
				var statusclid = eval("rectangulo"+a+".timer"+a);
				statusclid.text = hours+":"+minutes+":"+seconds;
			} else if (timer_type[a] == "DOWN") {
				// calculate values
				var elapsedTime = inicio_timer[a]-getTimer();
				if(elapsedTime < 0) {
					elapsedTime=0;
				}
				// hours
				var elapsedHours = floor(elapsedTime/3600000);
				var remaining = elapsedTime-(elapsedHours*3600000);
				// minutes
				var elapsedM = floor(remaining/60000);
				remaining = remaining-(elapsedM*60000);
				// seconds
				var elapsedS = floor(remaining/1000);
				// output to text box
				// add a 0 on the front of the numbers if the number is less than 10
				if (elapsedHours<10) {
					var hours = "0"+elapsedHours.toString();
				} else {
					var hours = elapsedHours.toString();
				}
				if (elapsedM<10) {
					var minutes = "0"+elapsedM.toString();
				} else {
					var minutes = elapsedM.toString();
				}
				if (elapsedS<10) {
					var seconds = "0"+elapsedS.toString();
				} else {
					var seconds = elapsedS.toString();
				}
				var statusclid = eval("rectangulo"+a+".timer"+a);
				statusclid.text = hours+":"+minutes+":"+seconds;
			}
		}
	}
};

setInterval(Timers, 1000);

function LocalSave(record,field,value) {
    var so = Object(SharedObject.getLocal(record));
    so.data[field] = value;
    so.flush();
};

function LocalLoad(record,field) {
    return Object(SharedObject.getLocal(record)).data[field];
};


MovieClip.prototype.changeledcolor = function(value,color1,color2,nroboton) {
    this.start = 1;
	this.color = 0;
	var button_number = ExtraeNumeroClip(this);

	_global.valorchangeledcolor[nroboton] = Number(value);
    colorhex1 = color1;
    colorhex2 = color2;
    color1 = parseInt(color1, 16);
    color2 = parseInt(color2, 16);
	this.arraycolor = new Array();
	this.arraycolor[0] = color1;
	this.arraycolor[1] = color2;
    var myColor = new Color(this);
    if (value==3) {
        this.onEnterFrame = function() {
		    this.start+=1;
            myColor.setRGB(this.arraycolor[this.color]);
			if(_global.enable_label_background == 1) {
		    	_root["rectangulo"+button_number]["textobg"].background = 1;
			    _root["rectangulo"+button_number]["textobg"].backgroundColor = this.arraycolor[this.color];
			}
			if(this.start>10) { 
				this.color = this.color +1;
				if(this.color > 1) {
					this.color = 0;
				}
				this.start = 0; 
			}
        };
    } else if (value==0 || value==1) {
        myColor.setRGB(this.arraycolor[value]);
		if(_global.enable_label_background == 1) {
		    _root["rectangulo"+button_number]["textobg"].background = 1;
		    _root["rectangulo"+button_number]["textobg"].backgroundColor = this.arraycolor[value];
		}
        delete this.onEnterFrame ;
	} else {
        myColor.setRGB(_global.color[0]);
		if(_global.enable_label_background == 1) {
	    	_root["rectangulo"+button_number]["textobg"].background = 1;
		    _root["rectangulo"+button_number]["textobg"].backgroundColor = _global.color[0];
		}
        delete this.onEnterFrame ;
    }
};


MovieClip.prototype.beginDrag = function(target, lock, l, t, r, b) {
	if (typeof(target) == "string") {
		target = eval(target);
	} else if (!(target instanceof MovieClip)) {
		b = r;
		r = t;
		t = l;
		l = lock;
		lock = target;
		target = this;
	}
	if (target.dragMethod) {
		target.endDrag();
	}
	target.dragMethod = {MM:target.onMouseMove};
	ASSetPropFlags(target, "dragMethod", 1, 1);
	target.addProperty("onMouseMove", arguments.callee.getMM, arguments.callee.setMM);
	ASSetPropFlags(target, "onMouseMove", 3);
	var constrain = (arguments.length>1);
	var off_x = 0, off_y = 0;
	if (!lock) {
		off_x = target._parent._xmouse-target._x;
		off_y = target._parent._ymouse-target._y;
	}
	target.dragMethod.drag = function() {
		target._x = target._parent._xmouse-off_x;
		target._y = target._parent._ymouse-off_y;
		if (constrain) {
			if (typeof(l) == "object") {
				t = l.ymin;
				r = l.xmax;
				b = l.ymax;
				l = l.xmin;
			}
			if (target._x<l) {
				target._x = l;
			} else if (target._x>r) {
				target._x = r;
			}
			if (target._y<t) {
				target._y = t;
			} else if (target._y>b) {
				target._y = b;
			}
		}
		updateAfterEvent();
	};
};

MovieClip.prototype.beginDrag.getMM = function() {
	this.dragMethod.drag();
	return this.dragMethod.MM;
};

MovieClip.prototype.beginDrag.setMM = function(f) {
	this.dragMethod.MM = f;
};

MovieClip.prototype.endDrag = function(target) {
	if (arguments.length) {
		if (typeof(target) == "string") {
			target = eval(target);
		}
	} else {
		target = this;
	}
	ASSetPropFlags(target, "onMouseMove", 0, 3);
	delete target.onMouseMove;
	if (target.dragMethod.MM) {
		target.onMouseMove = target.dragMethod.MM;
	}
	delete target.dragMethod;
	target.startDrag();
	// for _droptarget
	target.stopDrag();
};

MovieClip.prototype.flip = function(value) {
	this.TCG = 100;
	if (value) {
		this.onEnterFrame = function() {
			this._yscale = this._xscale*Math.sin(this.TCG/180*Math.PI);
			this.TCG += 10;
			if (this._yscale == this._xscale*-1) {
				this.stop();
				delete this.onEnterFrame;
				this._yscale = this._xscale;
			}
		};
	} else {
		delete this.onEnterFrame ;
		this._yscale = this.originalScale;
	}
};

MovieClip.prototype.shake = function(value) {
	if (value) {
		this.orgX = this._x;
		this.orgY = this._y;
		this.toggle = -1;
		this.onEnterFrame = function() {
			this.seconds = getTimer();
			this.modulo = this.seconds%2000;
			if (this.modulo<100) {
				if (this.viejotoggle == this.toggle) {
					this.toggle = this.toggle*-1;
				} else {
					this.viejotoggle = this.toggle;
				}
			}
			if (this.toggle>0) {
				this._x = this.orgX+Math.random()*(value*2)-value;
				this._y = this.orgY+Math.random()*(value*2)-value;
				this._rotation = this._rotation-Math.random()*3+Math.random()*3;
				updateAfterEvent();
			} else {
				this._x = this.orgX;
				this._y = this.orgY;
				this._rotation = 0;
			}
		};
	} else {
		delete this.onEnterFrame;
		this._x = this.orgX;
		this._y = this.orgY;
		this._rotation = 0;
	}
};

function makeStatusMail(origen) {
	if(_global.texto_mail[origen]!=undefined) {
		_level0.statusbar.status.text = _global.texto_mail[origen];
	}
}
function makeStatus(nroboton) {
	if (_global.texto_tip[nroboton] == undefined) {
		_global.texto_tip[nroboton] = _global.statusline;
	}
	_level0.statusbar.status.text = _global.texto_tip[nroboton];
}

function displaydetails(clip) {
	var button_number = ExtraeNumeroClip(clip);
	//		  _root["rectangulo"+pepe].flip(1);
	// 		  _root["resaltado"+pepe].flip(1);
	if (clip._currentframe>=3) {
		_root.detail._x = clip._parent._x;
		// FIXME get actual screen width or canvas size and
		// details windows widht to perform calculation of x asis.
		if ((_root.detail._x+200)>960) {
			_root.detail._x = 960-200;
		}
		_root.detail._y = clip._parent._y;
		if ((_root.detail._y+100)>600) {
			_root.detail._y = 600-100;
		}
	
		_root.detail._alpha = 90;
		_root.detail._visible = true;
		ind = clip._name.substring(6);
		ind = ExtraeNumeroClip(clip);
		if (st_originclid[ind] != undefined) {
			_level0.detail.label = vr.detail_from;
			_level0.detail.clid = st_originclid[ind];
		} else if (st_destinationclid[ind] != undefined) {
			_level0.detail.label = vr.detail_to;
			_level0.detail.clid = st_destinationclid[ind];
		} else {
			_root.detail.label = "Status:";
			_root.detail.clid = "none";
			_root.detail._visible = false;
			_root.superdetails.texto = _global.st_direction[ind];
			_root.superdetails._visible = true;
			_root.detail._visible = false;
		}
		if (st_duration[ind] != "") {
			_root.detail.duration = st_duration[ind];
		} else if (st_duration[ind] == "" && (st_originclid[ind] != "" || st_destinationclid[ind] != "")) {
			_root.detail.duration = "not answered";
		} else {
			_root.detail.duration = "";
		}

		var id = eval("queuemember." + ind);
		_global.superdetailstexttab2 = ""; 
		for (var a in id) {
			_global.superdetailstexttab2 += _global.queuemember[ind][a];
		} 
		_global.superdetailstexttab1 = _global.st_direction[ind];
		if (_global.superdetailstexttab1 == undefined)
        {
			_global.superdetailstexttab1 = vr.no_data_text;
			_root.superdetails.tab1.gotoAndStop(2);
			_root.superdetails.tab2.gotoAndStop(1);
			_root.superdetails.texto = _global.superdetailstexttab2;
		} else {
			_root.superdetails.tab1.gotoAndStop(1);
			_root.superdetails.tab2.gotoAndStop(2);
			_root.superdetails.texto = _global.superdetailstexttab1;
		}
	} // end if currentframe>=3
}

function doubleClick(clip) {
	// Funcion que detecta doble click y corta llamada

	if (lastclick == 0) {

        lastclick = getTimer();
	    lastclip = clip;

    } else {

        if(lastclip == clip) {

           logea ("double click "+clip);
           lastclick = 0;

		   if (_global.claveingresada == undefined) {
		   	  _root.codebox._visible = true;
			  Selection.setFocus(_root.codebox.claveform);
			  _root.codebox.swapDepths(_root.log);
			  return;
		   }

		   var numeroclip = ExtraeNumeroClip(clip);

	
		   if (defined(_global.restrict)) {
		   	  if (_global.restrict == numeroclip) {
				  logea("Authorized double click"+_global.restrict);
			  } else {
				  logea("Button Restriction in effect "+_global.restrict);
				  return;
			  }
		   }

			/* op_actions new_way 
	  		if (_global.authorized == true) {
			    envia_comando("doubleclick",clip,"");
			} else {
		   	    _root.codebox._visible = true;
			    Selection.setFocus(_root.codebox.claveform);
			    _root.codebox.swapDepths(_root.log);
			    return;
			}
			*/

 		   /* old_way */
		   if (clip._name.substring(0, 7) == "casilla") {
	 		   envia_comando("cortar",clip,"");
		   } else if (clip._name.substring(0,9)=="sobrecito") {
			   envia_comando("voicemail",clip,"0");
		   } else {
			   displaydetails(clip);
		   }
		   /* end */
	    }
    }
}

function dragStop(clip, x, y) {
    clip_arrastrado = undefined;
	clip._parent.swapDepths("_level0.tele"+masalto);
	//	clip.stopDrag();
	clip.endDrag();
	clip._x = x;
	clip._y = y;
	var destino = "";
	var origen = "";
	var origencompleto = "";
	//var origen = "" + eval(clip._name);
	//origen = origen.substring(12);
	origen = ExtraeNumeroClip(clip);


    var columna = int((_xmouse+ancho_boton+separacion)/(ancho_boton+separacion));
    var fila = int((_ymouse-30-separacion)/(alto_boton+separacion))+1;
    if (columna<1) {
            columna = 0;
    }
    if (columna>cuantas_columnas) {
         columna = 0;
    }
    if (fila>cuantas_filas) {
         fila = 0;
    }
    if (fila<1) {
         fila = 0;
    }
	if(columna>0 && fila>0) {
    	destino = ((columna*cuantas_filas)-cuantas_filas)+fila;
    }

    logea("droptarget "+clip._droptarget+" clip "+clip);
    logea("sobrehit  "+_global.sobrehit);

    /* USANDO DROPTARGET 
	for (s=1; s<clip._droptarget.length; s++) {
		var c = clip._droptarget.charAt(s);
		if (c == "/") {
			break;
		}
		if (c<"0" || c>"9") {
		} else {
			destino = destino+""+c;
		}
	}
    */


	clip._x = x;
	clip._y = y;
	flechita_frame = _root["rectangulo"+origen]["flecha"+origen]._currentframe;

	if(timer_type[origen]=="UP"||timer_type[origen]=="DOWN")
	{
		f_origen = 0;
	} else {
		f_origen = 1;
	}

	if(timer_type[destino]=="UP"||timer_type[destino]=="DOWN")
	{
		f_destino = 0;
	} else {
		f_destino = 1;
	}
//	f_origen       = timer_type[origen];
//	f_destino      = timer_type[destino];
	var extraclid = Trim(infotext1.clid_text.text);
	if (extraclid == undefined) {
		extraclid = "";
	}
	logea("f_origen "+f_origen+" f_destino "+f_destino);
	logea("origen "+origen+" destino "+destino);
	var done=0;
	if (_global.sobrehit != undefined) {
		var sobrecito = eval("rectangulo"+destino+".sobrecito"+destino);
		if(sobrecito._visible == true) {
			logea("como esta sobrecito: "+sobrecito._visible);
			destino = ExtraeNumeroClip(_global.sobrehit);
			logea("transfiero "+origen+" a destino voicemail "+destino);
			logea("_global.sobrehit = "+_global.sobrehit);
			envia_comando("tovoicemail", origen, destino);
			done = 1;
		}
	} 
//NICOX
	if (_global.flechahit != undefined) {
		var sobrecito = eval("rectangulo"+destino+".flecha"+destino);
		if(sobrecito._visible == true) {
			logea("como esta sobrecito: "+flecha._visible);
			destino = ExtraeNumeroClip(_global.flechahit);
			logea("transfiero "+origen+" a destino voicemail "+destino);
			logea("_global.flechahit = "+_global.flechahit);
			envia_comando("tospy", origen, destino);
			done = 1;
		}
	} 

	if (destino!="" && origen!=destino && done!=1) {
		if (f_origen==1 && f_destino==0) {
			// tranferencia 3 way a meetme
			logea("Attempt to conference "+origen+" with "+destino);
			envia_comando("conference", origen, destino);
		} else if ((f_origen==0 || flechita_frame == 1 || f_origen==-1) && (f_destino==1 || f_destino == 0)) {
			// transferencia normal
			logea("Attempt to transfer "+origen+" to "+destino);
			if (extraclid != "" && extraclid != "undefined") {
				extraclid = only_allowed_chars(extraclid);
				var comando = "ctransferir-"+extraclid+"-";
				infotext1.clidvalue = "";
			} else {
				var comando = "transferir";
			}
			if(_global.timeout_value>0) {
				comando= comando+"+"+_global.timeout_value+"+";
				logea("transfiero con timeout "+_global.timeout_value);
			}
			logea("comando "+comando);
			envia_comando(comando, origen, destino);
		} else {
			// originar llamado
			logea("Attempt to origin call from "+origen+" to "+destino);
			logea("f_origen "+f_origen+" f_destino "+f_destino);
			logea("timeout "+_global.timeout_value);
			logea("extraclid "+extraclid);
			if (extraclid != "" && extraclid != "undefined") {
				extraclid = only_allowed_chars(extraclid);
				var comando = "coriginate-"+extraclid+"-";
                infotext1.clid_text.text = "";
			} else {
				var comando = "originate";
			}
			if(_global.timeout_value > 0) {
				comando= comando+"+"+_global.timeout_value+"+";
				logea("origino con timeout "+_global.timeout_value);
			}
			envia_comando(comando, origen, destino);
		}
	} else {
		logea("drag to itself or single click");
    }
}

function only_allowed_chars(str) {
	allowed = "-abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789)(,";
	var temp = new String("");
	for (a=0; a<str.length; a++) {
		var letra = str.substr(a, 1);
		var pepe = allowed.IndexOf(letra);
		if (allowed.indexof(letra) == -1) {
			temp += " ";
		} else {
			temp += letra;
		}
	}
	return temp;
}

function MuestraLog() {
	if (log._visible == true) {
		log._visible = false;
//		_root.soundHolder.mySound.stop();
		//delete _root.soundHolder.mySound.stop();
	} else {
		log._visible = true;
		log.swapDepths(_root.codebox);
//		_root.soundHolder.mySound.start();
	}
}

function ExtraeNumeroClip(name) {
	var destino = "";
	name = name._name;
	for (var s = 0; s<name.length; s++) {
		var c = name.charAt(s);
		if (c<"0" || c>"9") {
		} else {
			destino = destino+""+c;
		}
		if (c == ".") {
			destino = "";
		}
	}
	return destino;
}

function dragClip(clip) {
    clip_arrastrado = clip;
	var numeroclip = ""+eval(clip._name);
	numeroclip = numeroclip.substring(12);
	startX = clip._x;
	startY = clip._y;
	clip._parent.swapDepths("_level0.tele"+masalto);
	clip.beginDrag(true);
	clip.onRelease = function() {
		dragStop(clip, startX, startY);
	};
	clip.onReleaseOutside = function() {
		dragStop(clip, startX, startY);
	};
}

envia_comando = function (comando, origen, destino) {
	if (comando != "bogus" && comando != "contexto" && comando != "restrict") {
		if (_global.restrict!=0) {
			if(comando == "cortar") {
				origen_number = ExtraeNumeroClip(origen);
			} else {
				origen_number = origen;
			}
			logea("Origen "+origen_number);
			logea("Destino "+destino);
			logea("Restrict "+_global.restrict);
			if(_global.restrict != undefined) {
			    if (_global.restrict == origen_number || _global.restrict == destino ) {
				    logea("Authorized envia_comando");
   			    } else {
				    logea("Button restriction in effect envia_comando "+_global.restrict);
				return;
			    }
			} else {
				// logea("global_restrict not defined?");
			}
		}
	}
	message = new XML();
	message_data = message.createElement("msg");
	if (_root.context.length>0) {
		agrega_contexto = "@"+context;
	}
	if (agrega_contexto == undefined) {
		agrega_contexto = "";
	}
	if (_level0.claveinput.secret == undefined) {
		_level0.claveinput.secret = "";
	}
	if (_global.claveingresada == undefined && ( comando != "contexto" && comando != "bogus" && comando != "dial" && comando != "restrict")) {
		_root.codebox._visible = true;
		Selection.setFocus(_root.codebox.claveform);
		_root.codebox.swapDepths(_root.log);
		return;
	}
	// var clave=_level0.claveinput.secret+_global.key;
	var clave = _global.claveingresada+_global.key;
	var md5clave = "";
	var md5clave = calcMD5(clave);
	if (comando == "contexto" || comando == "restrict") {
		md5clave = "";
	}
	message_data.attributes.data = origen+agrega_contexto+"|"+comando+destino+"|"+md5clave;
	message.appendChild(message_data);
	_global.sock.send(message);
	var clave = "";
};

function LTrim(str) {
	var whitespace = new String(" \t\n\r");
	var s = new String(str);
	if (whitespace.indexOf(s.charAt(0)) != -1) {
		var j = 0, i = s.length;
		while (j<i && whitespace.indexOf(s.charAt(j)) != -1) {
			j++;
		}
		s = s.substring(j, i);
	}
	return s;
}

function RTrim(str) {
	var whitespace = new String(" \t\n\r");
	var s = new String(str);
	if (whitespace.indexOf(s.charAt(s.length-1)) != -1) {
		var i = s.length-1;
		// Get length of string
		while (i>=0 && whitespace.indexOf(s.charAt(i)) != -1) {
			i--;
		}
		s = s.substring(0, i+1);
	}
	return s;
}

function Trim(str) {
	return RTrim(LTrim(str));
}

function setDND(obj, item) {
	var nroboton = ExtraeNumeroClip(obj);
	logea(item.caption+" for button "+nroboton);
	envia_comando("dnd", nroboton, nroboton);
}

function genera_selecttimeout() {

	_global.positionselect = 0;
	test = attachMovie("option","optionselected", getNextHighestDepth(),  {_x:800, _y:6});
	test._visible = true;
	test.legend = "No timeout";

	test.onPress = function() {
	     _root.despliega_select();
	};


	 for (a=0; a<5; a++) {
		var b=a+1;
		if (_global.opcionesTimeout[a] != undefined) {

			testa = attachMovie("option","option"+a, getNextHighestDepth(),  {_x:800, _y:(b*22)+6});
			testa.legend = _global.opcionesTimeout[a];
			testa._visible = false;


			testa.onRollOver = function() {
           	 	this.legend = "* "+this.legend;
        	};

   		 	testa.onRollOut = function() {
	            this.legend = this.legend.substring(2, this.legend.length);
    		};

			testa.onPress = function() {
	            this.legend = this.legend.substring(2, this.legend.length);
				var posicion = ExtraeNumeroClip(this);
				_global.timeout_value = _global.opcionesTimeoutSecs[posicion];
				_root.logea("timeout "+_global.timeout_value);
				_root.muestra_selecttimeout(0);
				_root.selectbox1.gotoAndStop(1);
				_root.optionselected._visible=true;
				_root.optionselected.legend = this.legend;
			};
		}
	}
};

function muestra_selecttimeout(value) {
	 for (a=0; a<5; a++) {
	 	var v = eval("_root.option"+a);
		if(value) {
			v._visible = true;
		} else {
			v._visible = false;
		}
	 }
};

function despliega_select() {
	_root.optionselected._visible=false;
	_root.selectbox1.gotoAndStop(2);
	_root.muestra_selecttimeout(1);

};

function base64_decode(opString) {
	if ( opString == undefined ) {
		return;
	} 
	var str = opString;
	var base64s = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
	var bits, bit1, bit2, bit3, bit4, i = 0;
	var decOut = "";
	for (i=0; i<str.length; i += 4) {
		bit1 = (base64s.indexOf(str.charAt(i)) & 0xff) << 18 ;
		bit2 = (base64s.indexOf(str.charAt(i+1)) & 0xff) << 12 ;
		bit3 = (base64s.indexOf(str.charAt(i+2)) & 0xff) << 6 ;
		bit4 = (base64s.indexOf(str.charAt(i+3)) & 0xff);
		bits = bit1 | bit2 | bit3 | bit4;
		decOut += String.fromCharCode((bits & 0xff0000) >> 16, (bits & 0xff00) >> 8, bits & 0xff);
	}
	if (str.charCodeAt(i-2) == 61) {
		return decOut.substring(0, decOut.length-2);
	} else if (str.charCodeAt(i-1) == 61) {
		return decOut.substring(0, decOut.length-1);
	} else {
		return decOut.substring(0, decOut.length);
	}
};



// MD5 ROUTINE
/*
 * Convert a 32-bit number to a hex string with ls-byte first
 */
var hex_chr = "0123456789abcdef";
// 
// somehow the expression (bitAND(b, c) | bitAND((~b), d)) didn't return coorect results on Mac
// for: 
// b&c = a8a20450, ((~b)&d) = 0101c88b, (bitAND(b, c) | bitAND((~b), d)) = a8a20450 <-- !!!
// looks like the OR is not executed at all.
//
// let's try to trick the P-code compiler into working with us... Prayer beads are GO!
// 
function bitOR(a, b) {
	var lsb = (a & 0x1) | (b & 0x1);
	var msb31 = (a >>> 1) | (b >>> 1);
	return (msb31 << 1) | lsb;
}
//  
// will bitXOR be the only one working...?
// Nope. XOR fails too if values with bit31 set are XORed. 
//
// Note however that OR (and AND and XOR?!) works alright for the statement
//   (msb31 << 1) | lsb
// even if the result of the left-shift operation has bit 31 set.
// So there might be an extra condition here (Guessmode turned on):
// Mac Flash fails (OR, AND and XOR) if either one of the input operands has bit31 set
// *and* both operands have one or more bits both set to 1. In other words: when both
// input bit-patterns 'overlap'.
// Stuff to munch on for the MM guys, I guess...
//
function bitXOR(a, b) {
	var lsb = (a & 0x1) ^ (b & 0x1);
	var msb31 = (a >>> 1) ^ (b >>> 1);
	return (msb31 << 1) | lsb;
}
// 
// bitwise AND for 32-bit integers. This uses 31 + 1-bit operations internally
// to work around bug in some AS interpreters. (Mac Flash!)
// 
function bitAND(a, b) {
	var lsb = (a & 0x1) & (b & 0x1);
	var msb31 = (a >>> 1) & (b >>> 1);
	return (msb31 << 1) | lsb;
	// return (a & b);
}
// 
// Add integers, wrapping at 2^32. This uses 16-bit operations internally
// to work around bugs in some AS interpreters. (Mac Flash!)
// 
function addme(x, y) {
	var lsw = (x & 0xFFFF)+(y & 0xFFFF);
	var msw = (x >> 16)+(y >> 16)+(lsw >> 16);
	return (msw << 16) | (lsw & 0xFFFF);
}
function rhex(num) {
	str = "";
	for (j=0; j<=3; j++) {
		str += hex_chr.charAt((num >> (j*8+4)) & 0x0F)+hex_chr.charAt((num >> (j*8)) & 0x0F);
	}
	return str;
}
/*
 * Convert a string to a sequence of 16-word blocks, stored as an array.
 * Append padding bits and the length, as described in the MD5 standard.
 */
function str2blks_MD5(str) {
	nblk = ((str.length+8) >> 6)+1;
	// 1 + (len + 8)/64
	blks = new Array(nblk*16);
	for (i=0; i<nblk*16; i++) {
		blks[i] = 0;
	}
	/*
				Input: 
				
				'willi' without the quotes.
				
				trace() Output on Intel (and MAC now?):
				
				see TXT files: *.Output.txt
				
				*/
	for (i=0; i<str.length; i++) {
		blks[i >> 2] |= str.charCodeAt(i) << (((str.length*8+i)%4)*8);
	}
	blks[i >> 2] |= 0x80 << (((str.length*8+i)%4)*8);
	var l = str.length*8;
	blks[nblk*16-2] = (l & 0xFF);
	blks[nblk*16-2] |= ((l >>> 8) & 0xFF) << 8;
	blks[nblk*16-2] |= ((l >>> 16) & 0xFF) << 16;
	blks[nblk*16-2] |= ((l >>> 24) & 0xFF) << 24;
	return blks;
}
/*
 * Bitwise rotate a 32-bit number to the left
 */
function rol(num, cnt) {
	return (num << cnt) | (num >>> (32-cnt));
}
/*
 * These functions implement the basic operation for each round of the
 * algorithm.
 */
function cmn(q, a, b, x, s, t) {
	return addme(rol((addme(addme(a, q), addme(x, t))), s), b);
}

function ff(a, b, c, d, x, s, t) {
	return cmn(bitOR(bitAND(b, c), bitAND((~b), d)), a, b, x, s, t);
}

function gg(a, b, c, d, x, s, t) {
	return cmn(bitOR(bitAND(b, d), bitAND(c, (~d))), a, b, x, s, t);
}

function hh(a, b, c, d, x, s, t) {
	return cmn(bitXOR(bitXOR(b, c), d), a, b, x, s, t);
}

function ii(a, b, c, d, x, s, t) {
	return cmn(bitXOR(c, bitOR(b, (~d))), a, b, x, s, t);
}
/*
 * Take a string and return the hex representation of its MD5.
 */
function calcMD5(str) {
	x = str2blks_MD5(str);
	a = 1732584193;
	b = -271733879;
	c = -1732584194;
	d = 271733878;
	var step;
	for (i=0; i<x.length; i += 16) {
		olda = a;
		oldb = b;
		oldc = c;
		oldd = d;
		step = 0;
		a = ff(a, b, c, d, x[i+0], 7, -680876936);
		d = ff(d, a, b, c, x[i+1], 12, -389564586);
		c = ff(c, d, a, b, x[i+2], 17, 606105819);
		b = ff(b, c, d, a, x[i+3], 22, -1044525330);
		a = ff(a, b, c, d, x[i+4], 7, -176418897);
		d = ff(d, a, b, c, x[i+5], 12, 1200080426);
		c = ff(c, d, a, b, x[i+6], 17, -1473231341);
		b = ff(b, c, d, a, x[i+7], 22, -45705983);
		a = ff(a, b, c, d, x[i+8], 7, 1770035416);
		d = ff(d, a, b, c, x[i+9], 12, -1958414417);
		c = ff(c, d, a, b, x[i+10], 17, -42063);
		b = ff(b, c, d, a, x[i+11], 22, -1990404162);
		a = ff(a, b, c, d, x[i+12], 7, 1804603682);
		d = ff(d, a, b, c, x[i+13], 12, -40341101);
		c = ff(c, d, a, b, x[i+14], 17, -1502002290);
		b = ff(b, c, d, a, x[i+15], 22, 1236535329);
		a = gg(a, b, c, d, x[i+1], 5, -165796510);
		d = gg(d, a, b, c, x[i+6], 9, -1069501632);
		c = gg(c, d, a, b, x[i+11], 14, 643717713);
		b = gg(b, c, d, a, x[i+0], 20, -373897302);
		a = gg(a, b, c, d, x[i+5], 5, -701558691);
		d = gg(d, a, b, c, x[i+10], 9, 38016083);
		c = gg(c, d, a, b, x[i+15], 14, -660478335);
		b = gg(b, c, d, a, x[i+4], 20, -405537848);
		a = gg(a, b, c, d, x[i+9], 5, 568446438);
		d = gg(d, a, b, c, x[i+14], 9, -1019803690);
		c = gg(c, d, a, b, x[i+3], 14, -187363961);
		b = gg(b, c, d, a, x[i+8], 20, 1163531501);
		a = gg(a, b, c, d, x[i+13], 5, -1444681467);
		d = gg(d, a, b, c, x[i+2], 9, -51403784);
		c = gg(c, d, a, b, x[i+7], 14, 1735328473);
		b = gg(b, c, d, a, x[i+12], 20, -1926607734);
		a = hh(a, b, c, d, x[i+5], 4, -378558);
		d = hh(d, a, b, c, x[i+8], 11, -2022574463);
		c = hh(c, d, a, b, x[i+11], 16, 1839030562);
		b = hh(b, c, d, a, x[i+14], 23, -35309556);
		a = hh(a, b, c, d, x[i+1], 4, -1530992060);
		d = hh(d, a, b, c, x[i+4], 11, 1272893353);
		c = hh(c, d, a, b, x[i+7], 16, -155497632);
		b = hh(b, c, d, a, x[i+10], 23, -1094730640);
		a = hh(a, b, c, d, x[i+13], 4, 681279174);
		d = hh(d, a, b, c, x[i+0], 11, -358537222);
		c = hh(c, d, a, b, x[i+3], 16, -722521979);
		b = hh(b, c, d, a, x[i+6], 23, 76029189);
		a = hh(a, b, c, d, x[i+9], 4, -640364487);
		d = hh(d, a, b, c, x[i+12], 11, -421815835);
		c = hh(c, d, a, b, x[i+15], 16, 530742520);
		b = hh(b, c, d, a, x[i+2], 23, -995338651);
		a = ii(a, b, c, d, x[i+0], 6, -198630844);
		d = ii(d, a, b, c, x[i+7], 10, 1126891415);
		c = ii(c, d, a, b, x[i+14], 15, -1416354905);
		b = ii(b, c, d, a, x[i+5], 21, -57434055);
		a = ii(a, b, c, d, x[i+12], 6, 1700485571);
		d = ii(d, a, b, c, x[i+3], 10, -1894986606);
		c = ii(c, d, a, b, x[i+10], 15, -1051523);
		b = ii(b, c, d, a, x[i+1], 21, -2054922799);
		a = ii(a, b, c, d, x[i+8], 6, 1873313359);
		d = ii(d, a, b, c, x[i+15], 10, -30611744);
		c = ii(c, d, a, b, x[i+6], 15, -1560198380);
		b = ii(b, c, d, a, x[i+13], 21, 1309151649);
		a = ii(a, b, c, d, x[i+4], 6, -145523070);
		d = ii(d, a, b, c, x[i+11], 10, -1120210379);
		c = ii(c, d, a, b, x[i+2], 15, 718787259);
		b = ii(b, c, d, a, x[i+9], 21, -343485551);
		a = addme(a, olda);
		b = addme(b, oldb);
		c = addme(c, oldc);
		d = addme(d, oldd);
	}
	return rhex(a)+rhex(b)+rhex(c)+rhex(d);
}


// TEA2

c2b = new Object();
c2b['\\000'] = 0;
c2b["\001"] = 1;
c2b["\002"] = 2;
c2b["\003"] = 3;
c2b["\004"] = 4;
c2b["\005"] = 5;
c2b["\006"] = 6;
c2b["\007"] = 7;
c2b["\010"] = 8;
c2b["\011"] = 9;
c2b["\012"] = 10;
c2b["\013"] = 11;
c2b["\014"] = 12;
c2b["\015"] = 13;
c2b["\016"] = 14;
c2b["\017"] = 15;
c2b["\020"] = 16;
c2b["\021"] = 17;
c2b["\022"] = 18;
c2b["\023"] = 19;
c2b["\024"] = 20;
c2b["\025"] = 21;
c2b["\026"] = 22;
c2b["\027"] = 23;
c2b["\030"] = 24;
c2b["\031"] = 25;
c2b["\032"] = 26;
c2b["\033"] = 27;
c2b["\034"] = 28;
c2b["\035"] = 29;
c2b["\036"] = 30;
c2b["\037"] = 31;
c2b["\040"] = 32;
c2b["\041"] = 33;
c2b['\042'] = 34;
c2b["\043"] = 35;
c2b["\044"] = 36;
c2b["\045"] = 37;
c2b["\046"] = 38;
c2b["\047"] = 39;
c2b["\050"] = 40;
c2b["\051"] = 41;
c2b["\052"] = 42;
c2b["\053"] = 43;
c2b["\054"] = 44;
c2b["\055"] = 45;
c2b["\056"] = 46;
c2b["\057"] = 47;
c2b["\060"] = 48;
c2b["\061"] = 49;
c2b["\062"] = 50;
c2b["\063"] = 51;
c2b["\064"] = 52;
c2b["\065"] = 53;
c2b["\066"] = 54;
c2b["\067"] = 55;
c2b["\070"] = 56;
c2b["\071"] = 57;
c2b["\072"] = 58;
c2b["\073"] = 59;
c2b["\074"] = 60;
c2b["\075"] = 61;
c2b["\076"] = 62;
c2b["\077"] = 63;
c2b["\100"] = 64;
c2b["\101"] = 65;
c2b["\102"] = 66;
c2b["\103"] = 67;
c2b["\104"] = 68;
c2b["\105"] = 69;
c2b["\106"] = 70;
c2b["\107"] = 71;
c2b["\110"] = 72;
c2b["\111"] = 73;
c2b["\112"] = 74;
c2b["\113"] = 75;
c2b["\114"] = 76;
c2b["\115"] = 77;
c2b["\116"] = 78;
c2b["\117"] = 79;
c2b["\120"] = 80;
c2b["\121"] = 81;
c2b["\122"] = 82;
c2b["\123"] = 83;
c2b["\124"] = 84;
c2b["\125"] = 85;
c2b["\126"] = 86;
c2b["\127"] = 87;
c2b["\130"] = 88;
c2b["\131"] = 89;
c2b["\132"] = 90;
c2b["\133"] = 91;
var pepe="\";
c2b[pepe] = 92;
var pepe="]";
c2b[pepe] = 93;
c2b["\136"] = 94;
c2b["\137"] = 95;
c2b["\140"] = 96;
c2b["\141"] = 97;
c2b["\142"] = 98;
c2b["\143"] = 99;
c2b["\144"] = 100;
c2b["\145"] = 101;
c2b["\146"] = 102;
c2b["\147"] = 103;
c2b["\150"] = 104;
c2b["\151"] = 105;
c2b["\152"] = 106;
c2b["\153"] = 107;
c2b["\154"] = 108;
c2b["\155"] = 109;
c2b["\156"] = 110;
c2b["\157"] = 111;
c2b["\160"] = 112;
c2b["\161"] = 113;
c2b["\162"] = 114;
c2b["\163"] = 115;
c2b["\164"] = 116;
c2b["\165"] = 117;
c2b["\166"] = 118;
c2b["\167"] = 119;
c2b["\170"] = 120;
c2b["\171"] = 121;
c2b["\172"] = 122;
c2b["\173"] = 123;
c2b["\174"] = 124;
c2b["\175"] = 125;
c2b["\176"] = 126;
c2b["\177"] = 127;
c2b["\200"] = 128;
c2b["\201"] = 129;
c2b["\202"] = 130;
c2b["\203"] = 131;
c2b["\204"] = 132;
c2b["\205"] = 133;
c2b["\206"] = 134;
c2b["\207"] = 135;
c2b["\210"] = 136;
c2b["\211"] = 137;
c2b["\212"] = 138;
c2b["\213"] = 139;
c2b["\214"] = 140;
c2b["\215"] = 141;
c2b["\216"] = 142;
c2b["\217"] = 143;
c2b["\220"] = 144;
c2b["\221"] = 145;
c2b["\222"] = 146;
c2b["\223"] = 147;
c2b["\224"] = 148;
c2b["\225"] = 149;
c2b["\226"] = 150;
c2b["\227"] = 151;
c2b["\230"] = 152;
c2b["\231"] = 153;
c2b["\232"] = 154;
c2b["\233"] = 155;
c2b["\234"] = 156;
c2b["\235"] = 157;
c2b["\236"] = 158;
c2b["\237"] = 159;
c2b["\240"] = 160;
c2b["\241"] = 161;
c2b["\242"] = 162;
c2b["\243"] = 163;
c2b["\244"] = 164;
c2b["\245"] = 165;
c2b["\246"] = 166;
c2b["\247"] = 167;
c2b["\250"] = 168;
c2b["\251"] = 169;
c2b["\252"] = 170;
c2b["\253"] = 171;
c2b["\254"] = 172;
c2b["\255"] = 173;
c2b["\256"] = 174;
c2b["\257"] = 175;
c2b["\260"] = 176;
c2b["\261"] = 177;
c2b["\262"] = 178;
c2b["\263"] = 179;
c2b["\264"] = 180;
c2b["\265"] = 181;
c2b["\266"] = 182;
c2b["\267"] = 183;
c2b["\270"] = 184;
c2b["\271"] = 185;
c2b["\272"] = 186;
c2b["\273"] = 187;
c2b["\274"] = 188;
c2b["\275"] = 189;
c2b["\276"] = 190;
c2b["\277"] = 191;
c2b["\300"] = 192;
c2b["\301"] = 193;
c2b["\302"] = 194;
c2b["\303"] = 195;
c2b["\304"] = 196;
c2b["\305"] = 197;
c2b["\306"] = 198;
c2b["\307"] = 199;
c2b["\310"] = 200;
c2b["\311"] = 201;
c2b["\312"] = 202;
c2b["\313"] = 203;
c2b["\314"] = 204;
c2b["\315"] = 205;
c2b["\316"] = 206;
c2b["\317"] = 207;
c2b["\320"] = 208;
c2b["\321"] = 209;
c2b["\322"] = 210;
c2b["\323"] = 211;
c2b["\324"] = 212;
c2b["\325"] = 213;
c2b["\326"] = 214;
c2b["\327"] = 215;
c2b["\330"] = 216;
c2b["\331"] = 217;
c2b["\332"] = 218;
c2b["\333"] = 219;
c2b["\334"] = 220;
c2b["\335"] = 221;
c2b["\336"] = 222;
c2b["\337"] = 223;
c2b["\340"] = 224;
c2b["\341"] = 225;
c2b["\342"] = 226;
c2b["\343"] = 227;
c2b["\344"] = 228;
c2b["\345"] = 229;
c2b["\346"] = 230;
c2b["\347"] = 231;
c2b["\350"] = 232;
c2b["\351"] = 233;
c2b["\352"] = 234;
c2b["\353"] = 235;
c2b["\354"] = 236;
c2b["\355"] = 237;
c2b["\356"] = 238;
c2b["\357"] = 239;
c2b["\360"] = 240;
c2b["\361"] = 241;
c2b["\362"] = 242;
c2b["\363"] = 243;
c2b["\364"] = 244;
c2b["\365"] = 245;
c2b["\366"] = 246;
c2b["\367"] = 247;
c2b["\370"] = 248;
c2b["\371"] = 249;
c2b["\372"] = 250;
c2b["\373"] = 251;
c2b["\374"] = 252;
c2b["\375"] = 253;
c2b["\376"] = 254;
c2b["\377"] = 255;
b2c = new Object();
for (b in c2b) {
	b2c[c2b[b]] = b;
}


// ascii to 6-bit bin to ascii
a2b = new Object();
a2b["A"] = 0;
a2b["B"] = 1;
a2b["C"] = 2;
a2b["D"] = 3;
a2b["E"] = 4;
a2b["F"] = 5;
a2b["G"] = 6;
a2b["H"] = 7;
a2b["I"] = 8;
a2b["J"] = 9;
a2b["K"] = 10;
a2b["L"] = 11;
a2b["M"] = 12;
a2b["N"] = 13;
a2b["O"] = 14;
a2b["P"] = 15;
a2b["Q"] = 16;
a2b["R"] = 17;
a2b["S"] = 18;
a2b["T"] = 19;
a2b["U"] = 20;
a2b["V"] = 21;
a2b["W"] = 22;
a2b["X"] = 23;
a2b["Y"] = 24;
a2b["Z"] = 25;
a2b["a"] = 26;
a2b["b"] = 27;
a2b["c"] = 28;
a2b["d"] = 29;
a2b["e"] = 30;
a2b["f"] = 31;
a2b["g"] = 32;
a2b["h"] = 33;
a2b["i"] = 34;
a2b["j"] = 35;
a2b["k"] = 36;
a2b["l"] = 37;
a2b["m"] = 38;
a2b["n"] = 39;
a2b["o"] = 40;
a2b["p"] = 41;
a2b["q"] = 42;
a2b["r"] = 43;
a2b["s"] = 44;
a2b["t"] = 45;
a2b["u"] = 46;
a2b["v"] = 47;
a2b["w"] = 48;
a2b["x"] = 49;
a2b["y"] = 50;
a2b["z"] = 51;
a2b["0"] = 52;
a2b["1"] = 53;
a2b["2"] = 54;
a2b["3"] = 55;
a2b["4"] = 56;
a2b["5"] = 57;
a2b["6"] = 58;
a2b["7"] = 59;
a2b["8"] = 60;
a2b["9"] = 61;
a2b["+"] = 62;
a2b["_"] = 63;
b2a = new Object();
for (b in a2b) {
	b2a[a2b[b]] = ''+b;
}

function binary2ascii(s) {
	return bytes2ascii(blocks2bytes(s));
}
function binary2str(s) {
	return bytes2str(blocks2bytes(s));
}
function ascii2binary(s) {
	return bytes2blocks(ascii2bytes(s));
}
function str2binary(s) {
	return bytes2blocks(str2bytes(s));
}
function str2bytes(s) {
	var is = 0;
	var ls = s.length;
	var b = new Array();
	while (1) {
		if (is>=ls) {
			break;
		}
		var pepe=s.charAt(is);
		if (c2b[s.charAt(is)] == null) {
			b[is] = 0xF7;
		} else {
			b[is] = c2b[s.charAt(is)];
		}
		is++;
	}
	return b;
}
function bytes2str(b) {
	var ib = 0;
	var lb = b.length;
	var s = '';
	while (1) {
		if (ib>=lb) {
			break;
		}
		if (b2c[0xFF & b[ib]]!=undefined) {
			s += b2c[0xFF & b[ib]];
		}
		ib++;
	}
	return s;
}
function ascii2bytes(a) {
	var ia = -1;
	var la = a.length;
	var ib = 0;
	var b = new Array();
	var carry;
	while (1) {
		// reads 4 chars and produces 3 bytes
		while (1) {
			ia++;
			if (ia>=la) {
				return b;
			}
			if (a2b[a.charAt(ia)] != null) {
				break;
			}
		}
		b[ib] = a2b[a.charAt(ia)] << 2;
		while (1) {
			ia++;
			if (ia>=la) {
				return b;
			}
			if (a2b[a.charAt(ia)] != null) {
				break;
			}
		}
		carry = a2b[a.charAt(ia)];
		b[ib] |= carry >>> 4;
		ib++;
		carry = 0xF & carry;
		if (carry == 0 && ia == (la-1)) {
			return b;
		}
		b[ib] = carry << 4;
		while (1) {
			ia++;
			if (ia>=la) {
				return b;
			}
			if (a2b[a.charAt(ia)] != null) {
				break;
			}
		}
		carry = a2b[a.charAt(ia)];
		b[ib] |= carry >>> 2;
		ib++;
		carry = 3 & carry;
		if (carry == 0 && ia == (la-1)) {
			return b;
		}
		b[ib] = carry << 6;
		while (1) {
			ia++;
			if (ia>=la) {
				return b;
			}
			if (a2b[a.charAt(ia)] != null) {
				break;
			}
		}
		b[ib] |= a2b[a.charAt(ia)];
		ib++;
	}
	return b;
}
function bytes2ascii(b) {
	var ib = 0;
	var lb = b.length;
	var s = '';
	var b1;
	var b2;
	var b3;
	var carry;
	while (1) {
		// reads 3 bytes and produces 4 chars
		if (ib>=lb) {
			break;
		}
		b1 = 0xFF & b[ib];
		s += b2a[63 & (b1 >>> 2)];
		carry = 3 & b1;
		ib++;
		if (ib>=lb) {
			s += b2a[carry << 4];
			break;
		}
		b2 = 0xFF & b[ib];
		s += b2a[(0xF0 & (carry << 4)) | (b2 >>> 4)];
		carry = 0xF & b2;
		ib++;
		if (ib>=lb) {
			s += b2a[carry << 2];
			break;
		}
		b3 = 0xFF & b[ib];
		s += b2a[(60 & (carry << 2)) | (b3 >>> 6)]+b2a[63 & b3];
		ib++;
		if (ib%36 == 0) {
			s += "\n";
		}
	}
	return s;
}
function bytes2blocks(bytes) {
	var blocks = new Array();
	var ibl = 0;
	var iby = 0;
	var nby = bytes.length;
	while (1) {
		blocks[ibl] = (0xFF & bytes[iby]) << 24;
		iby++;
		if (iby>=nby) {
			break;
		}
		blocks[ibl] |= (0xFF & bytes[iby]) << 16;
		iby++;
		if (iby>=nby) {
			break;
		}
		blocks[ibl] |= (0xFF & bytes[iby]) << 8;
		iby++;
		if (iby>=nby) {
			break;
		}
		blocks[ibl] |= 0xFF & bytes[iby];
		iby++;
		if (iby>=nby) {
			break;
		}
		ibl++;
	}
	return blocks;
}
function blocks2bytes(blocks) {
	var bytes = new Array();
	var iby = 0;
	var ibl = 0;
	var nbl = blocks.length;
	while (1) {
		if (ibl>=nbl) {
			break;
		}
		bytes[iby] = 0xFF & (blocks[ibl] >>> 24);
		iby++;
		bytes[iby] = 0xFF & (blocks[ibl] >>> 16);
		iby++;
		bytes[iby] = 0xFF & (blocks[ibl] >>> 8);
		iby++;
		bytes[iby] = 0xFF & blocks[ibl];
		iby++;
		ibl++;
	}
	return bytes;
}
function digest_pad(bytearray) {
	var newarray = new Array();
	var ina = 0;
	var iba = 0;
	var nba = bytearray.length;
	var npads = 15-(nba%16);
	newarray[ina] = npads;
	ina++;
	while (iba<nba) {
		newarray[ina] = bytearray[iba];
		ina++;
		iba++;
	}
	var ip = npads;
	while (ip>0) {
		newarray[ina] = 0;
		ina++;
		ip--;
	}
	return newarray;
}
function pad(bytearray) {
	var newarray = new Array();
	var ina = 0;
	var iba = 0;
	var nba = bytearray.length;
	var npads = 7-(nba%8);
	newarray[ina] = (0xF8 & rand_byte()) | (7 & npads);
	ina++;
	while (iba<nba) {
		newarray[ina] = bytearray[iba];
		ina++;
		iba++;
	}
	var ip = npads;
	while (ip>0) {
		newarray[ina] = rand_byte();
		ina++;
		ip--;
	}
	return newarray;
}
function rand_byte() {
	return Math.floor(256*Math.random());
	if (!rand_byte_already_called) {
		var now = new Date();
		seed = now.milliseconds;
		rand_byte_already_called = true;
	}
	seed = (1029*seed+221591)%1048576;
	return Math.floor(seed/4096);
}
function unpad(bytearray) {
	var iba = 0;
	var newarray = new Array();
	var ina = 0;
	var npads = 0x7 & bytearray[iba];
	iba++;
	var nba = bytearray.length-npads;
	while (iba<nba) {
		newarray[ina] = bytearray[iba];
		ina++;
		iba++;
	}
	return newarray;
}
function asciidigest(str) {
	return binary2ascii(binarydigest(str));
}
function binarydigest(str, keystr) {
	var key = new Array();
	key[0] = 0x61626364;
	key[1] = 0x62636465;
	key[2] = 0x63646566;
	key[3] = 0x64656667;
	var c0 = new Array();
	c0[0] = 0x61626364;
	c0[1] = 0x62636465;
	var c1 = new Array();
	c1 = c0;
	var v0 = new Array();
	var v1 = new Array();
	var swap;
	var blocks = new Array();
	blocks = bytes2blocks(digest_pad(str2bytes(str)));
	var ibl = 0;
	var nbl = blocks.length;
	while (1) {
		if (ibl>=nbl) {
			break;
		}
		v0[0] = blocks[ibl];
		ibl++;
		v0[1] = blocks[ibl];
		ibl++;
		v1[0] = blocks[ibl];
		ibl++;
		v1[1] = blocks[ibl];
		ibl++;
		c0 = tea_code(xor_blocks(v0, c0), key);
		c1 = tea_code(xor_blocks(v1, c1), key);
		swap = c0[0];
		c0[0] = c0[1];
		c0[1] = c1[0];
		c1[0] = c1[1];
		c1[1] = swap;
	}
	var concat = new Array();
	concat[0] = c0[0];
	concat[1] = c0[1];
	concat[2] = c1[0];
	concat[3] = c1[1];
	return concat;
}
function encrypt(str, keystr) {
	var key = new Array();
	key = binarydigest(keystr);
	var blocks = new Array();
	blocks = bytes2blocks(pad(str2bytes(str)));
	var ibl = 0;
	var nbl = blocks.length;
	// Initial Value for CBC mode = "abcdbcde". Retain for interoperability.
	var c = new Array();
	c[0] = 0x61626364;
	c[1] = 0x62636465;
	var v = new Array();
	var cblocks = new Array();
	var icb = 0;
	while (1) {
		if (ibl>=nbl) {
			break;
		}
		v[0] = blocks[ibl];
		ibl++;
		v[1] = blocks[ibl];
		ibl++;
		c = tea_code(xor_blocks(v, c), key);
		cblocks[icb] = c[0];
		icb++;
		cblocks[icb] = c[1];
		icb++;
	}
	return binary2ascii(cblocks);
}
function decrypt(ascii, keystr) {
	var key = new Array();
	key = binarydigest(keystr);
	var cblocks = new Array();
	cblocks = ascii2binary(ascii);
	var icbl = 0;
	var ncbl = cblocks.length;
	var lastc = new Array();
	lastc[0] = 0x61626364;
	lastc[1] = 0x62636465;
	var v = new Array();
	var c = new Array();
	var blocks = new Array();
	var ibl = 0;
	while (1) {
		if (icbl>=ncbl) {
			break;
		}
		c[0] = cblocks[icbl];
		icbl++;
		c[1] = cblocks[icbl];
		icbl++;
		v = xor_blocks(lastc, tea_decode(c, key));
		blocks[ibl] = v[0];
		ibl++;
		blocks[ibl] = v[1];
		ibl++;
		lastc[0] = c[0];
		lastc[1] = c[1];
	}
	return bytes2str(unpad(blocks2bytes(blocks)));
}
function xor_blocks(blk1, blk2) {
	var blk = new Array();
	blk[0] = blk1[0] ^ blk2[0];
	blk[1] = blk1[1] ^ blk2[1];
	return blk;
}
function tea_code(v, k) {
	var v0 = v[0];
	var v1 = v[1];
	var k0 = k[0];
	var k1 = k[1];
	var k2 = k[2];
	var k3 = k[3];
	var sum = 0;
	var n = 32;
	while (n-->0) {
		sum -= 1640531527;
		// TEA magic number 0x9e3779b9 
		sum = sum | 0;
		v0 += ((v1 << 4)+k0) ^ (v1+sum) ^ ((v1 >>> 5)+k1);
		v1 += ((v0 << 4)+k2) ^ (v0+sum) ^ ((v0 >>> 5)+k3);
	}
	var w = new Array();
	w[0] = v0 | 0;
	w[1] = v1 | 0;
	return w;
}
function tea_decode(v, k) {
	var v0 = v[0];
	var v1 = v[1];
	var k0 = k[0];
	var k1 = k[1];
	var k2 = k[2];
	var k3 = k[3];
	var sum = 0;
	var n = 32;
	sum = -957401312;
	while (n-->0) {
		v1 -= ((v0 << 4)+k2) ^ (v0+sum) ^ ((v0 >>> 5)+k3);
		v0 -= ((v1 << 4)+k0) ^ (v1+sum) ^ ((v1 >>> 5)+k1);
		sum += 1640531527;
		sum = sum | 0;
	}
	var w = new Array();
	w[0] = v0 | 0;
	w[1] = v1 | 0;
	return w;
}

Key.addListener(Key);

Key.onKeyDown = function(){
	var incremento = 1;
	var tecla = Key.getCode();

	if(tecla == 16) {
		_global.shift = 1;
	}

	if(tecla == 40) // DOWN
	{
		// Select next button DOWN
    	myapaga = eval('_root.resaltado'+_global.rectanguloprendido);
       	dif1 = (_global.rectanguloprendido) % _root.cuantas_filas;
		incremento = 1;
		if(dif1 == 0) {
			// It changed the column, increment it again
			incremento = incremento - _root.cuantas_filas;
		}
        proximo = _global.rectanguloprendido + incremento;
        var myresa = eval('_root.resaltado'+proximo);
		if(_global.rectanguloprendido != _global.restrict) {
        myapaga._visible = false;
		}
        myresa._visible = true;
        _global.rectanguloprendido = proximo;
        _root.makeStatus(proximo);
	}
	if(tecla == 38) // UP
	{
		// Select next button UP
    	myapaga = eval('_root.resaltado'+_global.rectanguloprendido);
       	dif1 = (_global.rectanguloprendido-1) % _root.cuantas_filas;
		incremento = -1;
		if(dif1 == 0) {
			// It changed the column, increment it again
			incremento = incremento + _root.cuantas_filas;
		}
        proximo = _global.rectanguloprendido + incremento;
        var myresa = eval('_root.resaltado'+proximo);
		if(_global.rectanguloprendido != _global.restrict) {
        myapaga._visible = false;
		}
        myresa._visible = true;
        _global.rectanguloprendido = proximo;
        _root.makeStatus(proximo);
	}
	if(tecla == 37) // LEFT
	{

		if (_root.superdetails._visible == true) {
			var tab = _root.superdetails.tab1._currentframe;
		    if(tab == 2) {
		        _root.superdetails.tab1.gotoAndStop(1);
		        _root.superdetails.tab2.gotoAndStop(2);
		        _root.superdetails.texto = _global.superdetailstexttab1;
		    } else {
		        _root.superdetails.tab1.gotoAndStop(2);
		        _root.superdetails.tab2.gotoAndStop(1);
		        _root.superdetails.texto = _global.superdetailstexttab2;
		    }
		} else {
			// Select next button on the LEFT
	    	myapaga = eval('_root.resaltado'+_global.rectanguloprendido);
	       	incremento = _root.cuantas_filas;
			diferencia = _global.rectanguloprendido % _root.cuantas_filas;
	        proximo = _global.rectanguloprendido - incremento;

        	if(proximo < 1) {
				proximo = ((_root.cuantas_columnas - 1) * _root.cuantas_filas)+diferencia;
       	 	}
        	var myresa = eval('_root.resaltado'+proximo);
			if(_global.rectanguloprendido != _global.restrict) {
        	myapaga._visible = false;
			}
        	myresa._visible = true;
        	_global.rectanguloprendido = proximo;
        	_root.makeStatus(proximo);
		}
	}

	if(tecla == 39) // RIGHT
	{
		if(_root.detail._visible == true) {
			_root.superdetails._visible = true;
			_root.detail._visible = false;
		} else if (_root.superdetails._visible == true) {
			var tab = _root.superdetails.tab1._currentframe;
		    if(tab == 2) {
		        _root.superdetails.tab1.gotoAndStop(1);
		        _root.superdetails.tab2.gotoAndStop(2);
		        _root.superdetails.texto = _global.superdetailstexttab1;
		    } else {
		        _root.superdetails.tab1.gotoAndStop(2);
		        _root.superdetails.tab2.gotoAndStop(1);
		        _root.superdetails.texto = _global.superdetailstexttab2;
		    }
		} else {
			// Select next button on the RIGHT
	    	myapaga = eval('_root.resaltado'+_global.rectanguloprendido);
	        total = _root.cuantas_filas * _root.cuantas_columnas;
        	incremento = _root.cuantas_filas;
			diferencia = _global.rectanguloprendido % _root.cuantas_filas;
	        proximo = _global.rectanguloprendido + incremento;

	        if(proximo > total) {
	            proximo = 1+diferencia-1;
	        }
	        var myresa = eval('_root.resaltado'+proximo);
			if(_global.rectanguloprendido != _global.restrict) {
	        myapaga._visible = false;
			}
	        myresa._visible = true;
	        _global.rectanguloprendido = proximo;
	        _root.makeStatus(proximo);
		}
	}
	if(tecla == 9) // TAB
	{
		myapaga = eval('_root.resaltado'+_global.rectanguloprendido);
		total = _root.cuantas_filas * _root.cuantas_columnas;
		if(_global.shift == 1) {
			incremento = -1;
		} else {
			incremento = 1;
		}
		proximo = _global.rectanguloprendido + incremento;

	 	if(proximo > total) {
			proximo = 1;
		}
		if(proximo < 1) {
			proximo = total;
		}
		var myresa = eval('_root.resaltado'+proximo);
		if(_global.rectanguloprendido == _global.restrict) {
	  	myapaga._visible = false;
		}
	   	myresa._visible = true;
	   	_global.rectanguloprendido = proximo;
	   	_root.makeStatus(proximo);
	}

	if(tecla == 27)	// ESC
	{
		_root.codebox._visible = false;
		_root.log._visible = false;
		_root.detail._visible = false;
		_root.superdetails._visible = false;
	}

	if(tecla == 18) // ALT
	{
		var myon = _global.rectanguloprendido;
		if(myon>0) {
			var myclip = eval('_level0.rectangulo'+myon+'.flecha'+myon);
			_root.displaydetails(myclip);
		}
	}

	if(tecla == 13) // ENTER
	{
		if(_root.codebox._visible == true) {
			// The security code box is visible, sends code and hides it
			_global.claveingresada = _root.codebox.claveform.text;
			_root.codebox._visible = false;
			_root.envia_comando('bogus', 0, 0);
		} else {
			// The security code is not visible, open detail windows of
			// highlighted button
			var myon = _global.rectanguloprendido;
			if(myon>0) {
				var myclip = eval('_level0.rectangulo'+myon+'.flecha'+myon);
				_root.displaydetails(myclip);
			}
		}
	}


};

Key.onKeyUp = function(){
	var tecla = Key.getCode();
	if(tecla == 16) {
		_global.shift = 0;
	}
};




// loadMovieNum("icono1.swf",1);

Inicia_Variables();
dibuja();
Detiene_Peliculas();
conecta();

EndOfActionScript

# Saves the movie
$movie->nextFrame();
$movie->save("operator_panel.swf",9);



