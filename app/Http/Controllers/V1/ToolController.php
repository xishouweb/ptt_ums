<?php
/**
 * Created by PhpStorm.
 * User: Kana
 * Date: 2018/9/13
 * Time: 16:41
 */

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\Captcha;
use App\Models\Contract;
use App\Models\DataCache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use QL\QueryList;

class ToolController extends Controller
{
    const JIA_QUN_LA_APPID = 'BJBTK-1000001';
    const JIA_QUN_LA_SECRET = 'BJBTK-a5195c503957b7e8c024454a0f8ea2c5';

    const TIP_SYMBLOS = ['eth', 'btc'];
    const TIPS = [
        'btc' => [
            '2' => '芝麻开花步步高高，还没上车后悔了',
            '5' => '九万里风鹏正举，不止休，牛市归来？',
            '10' => '时人不识别凌云木，只待凌云始道高',
            '20' => '一根穿云箭，千军万马来相见',
        ],
        'eth' => [
            '2' => 'K线有绿红，涨跌放心中',
            '5' => '早也盼晚也盼，握住机会把钱赚',
            '10' => '平地惊声雷，一阳改三观',
            '20' => '能量朝稳步走高，五线向上牵大牛',
        ],
        'other' => [
            '10' => '守的云开见天明~',
            '20' => '上山爬坡缓慢走，烘云托月是小牛',
            '50' => '趋势之力不可阻，千金难买牛回头',
            '100' => '会当凌绝顶，一览众山小',
            '200' => '一根大阳线，千军万马来相见',
        ],
    ];

    const OTHER_SYMBOL =  ["dc","cusdt","ceth","dt","safe","cht","snet","fdz","imt","eosdac","xmc","vns","eop","eon","meetone","kt","keos","lrn","add","cet","buc","atd","palt","bqt","horus","iq","black","eox","inb","pt","dk","ode","rstr","zib","dcv","pct","ddt","tmo","karma","kat","ade","busd","vee","beam","ktn","news","usdteth","usdttron","plo","da0001","ltk","hsn", "nmc","trc","ppc","nvc","ftc","mnc","frc","ixc","btb","wdc","dgc","glc","arg","mec","ifc","xpm","anc","csc","emd","qrk","zet","src","sxc","tag","flo","uno","dtc","bet","dem","dmd","orb","tips","moon","dime","42","vtc","smc","rdd","nyan","utc","pot","blc","max","xcp","mint","dope","aur","ptc","cash","pnd","ufo","blk","pho","zeit","xmy","note","skc","emc2","btcs","ecc","mona","rby","bela","slr","efl","nlg","xpd","plnc","pop","bits","qbc","blu","maid","xbc","talk","nyc","pink","drm","enrg","vrc","lcp","cure","super","bost","moto","bsd","c2","aby","start","kore","xdn","bbr","thc","brit","xst","clam","ioc","xcn","carbon","cann","tit","vta","hyp","j","sjcx","emc","rbbt","burst","game","n8v","ubq","opal","acoin","fldc","bitusd","bitcny","bitbtc","usnbt","xmg","excl","troll","bsty","dp","pxi","nsr","spr","rbt","mue","block","crw","bay","gcn","xqn","ok","xpy","bitgold","smly","bitsilver","kobo","bitb","geo","wbb","grc","xco","ldoge","song","crave","pura","ntrn","xaur","cf","aib","sphr","medic","bub","xsd","unit","pkb","arb","bta","adc","snrg","biteur","fjc","gxx","xra","creva","irl","zny","bsc","acp","sprts","hnc","manna","axiom","aeon","tx","gcc","ams","agrs","euc","gcr","shift","vec2","boli","bcy","pak","exp","sib","swing","fct","duo","sandg","shnd","1337","scrt","dft","obits","amp","club","adz","moin","av","egc","crb","rads","ltcr","yoc","sls","frn","evil","sft","rbies","trump","meme","ims","hodl","bigup","neva","bumba","rvr","pex","cab","mojo","edrc","post","bern","qwark","esp","fuzz","xhi","arco","xbtc21","el","zur","611","2give","xptx","lana","ponzi","mxt","ctl","icoo","pwr","ion","hvco","gb","rise","chess","lbc","synx","cj","heat","sbd","bit","ele","krb","aces","taj","edc","xp","vlt","kb3","golf","lmc","btdx","nlc2","vrm","zyd","jin","plu","tell","dlc","mst","proud","seq","1st","pepecash","levo","arc","dmc","asafe","zcl","lkk","iop","vrs","hush","kurt","pasc","ent","incnt","golos","nxc","vsl","pcs","gbyte","posw","luna","frgc","dar","iflt","xspec","xstc","benji","ccrb","vidz","icob","ibank","frst","mgm","wct","icon","boat","ery","els","gbg","cnt","mscn","mln","time","argus","rns","swt","milo","music","zer","honey","netko","dyn","tks","mer","taas","soar","edg","b@","dtb","uni","xlr","imx","dbix","gup","usc","blazr","hpc","zeni","cxt","conx","xby","trst","wgo","proc","scs","btx","volt","gno","tkn","rain","hmq","iti","mne","cnnc","dice","insn","hallo","ant","pzm","rlt","eqt","dmb","nanox","may","sumo","etbs","v","etp","ebst","adk","ptoy","veri","eca","qrl","ett","mgo","ammo","nro","ppy","miota","myst","more","linx","adl","zrc","glt","nmr","unify","xel","mrt","dcy","onx","btpl","atcc","good","bro","flash","efyt","bria","d","socc","adt","ping","wgr","ecob","plbt","jet","women","bdl","rup","pcn","san","ter","cvn","linda","ethos","sta","coal","lbtc","part","smart","skin","toa","plr","sigt","oct","pbt","cc","emb","ixt","gsr","birds","crm","era","kek","stx","minex","wink","mao","tix","dcn","rupx","shdw","onion","cat","ads","ift","xcxt","riya","tcc","bln","myb","tfl","dalc","acc","bbp","bq","sigma","xmcc","brat","opt","sur","ltcu","mcc","avt","hvn","vsx","vivo","ocl","rex","buzz","credo","ind","xpa","scl","atb","iqt","pro","bmc","elix","xbl","day","pix","coss","csno","rvt","itz","tgt","orme","kln","colx","tzc","odn","cob","rec","msd","bis","voise","xin","atm","kick","rhoc","inxt","flik","cnx","real","hbt","cct","bco","ppp","alis","btcz","hgt","ctic3","bsn","zsc","ecash","sic","ats","pipl","cag","trct","art","xgox","evr","dutch","otn","drt","b2x","etg","blue","runners","life","kcs","exrn","poll","la","btcred","prg","bos","js","elite","itt","ieth","pirl","lux","dov","placo","btcm","ella","fyp","ebtc","ibtc","grid","atl","mnx","sono","for","xsh","eltcoin","dsr","ukg","nio","phr","inn","erc20","tie","grim","epy","dbet","hst","ufr","stu","prix","lthn","payx","grx","sgr","viu","xlq","gbx","wc","b2b","pnx","dna","ink","zzc","tsl","mag","spank","vot","monk","flixx","wish","evc","cco","qbt","drgn","pfr","pre","bcdn","capp","ero","jiyo","send","bon","nuko","snov","bwk","cms","wand","spf","cred","sct","uqc","ignis","hwc","pkt","fil","trdt","amm","loc","wrc","ytn","star","dtr","med","dew","ngc","dai","sphtx","bnty","dim","cpay","html","neu","play","hpy","pylnt","stak","fdx","tau","blt","cov","can","gcs","cl","opc","cfun","polis","hkn","zap","aix","rebl","god","utt","cdx","bdg","lev","krm","atn","sxdt","sxut","swtc","vzt","kzc","bca","tel","ic","weth","sense","moac","tokc","ait","qube","spc","ore","horse","arct","mvc","nox","ipl","idxm","gat","sexc","mobi","read","tnc","c20","ary","crpt","spk","cv","tbx","prs","idh","qbic","guess","aid","eve","bpt","axpr","trac","slt","fota","grlc","nkc","cofi","eql","hlc","oc","candy","sms","vlc","btw","trf","pareto","jnt","chsb","adb","dmt","ing","swm","drpu","esz","dxt","ucash","mntp","jew","mlm","loci","jc","bio","sup","tig","utnp","acat","evn","rmt","dth","fsn","mwat","ntk","gem","nec","lcc","stq","tdx","cpy","rem","exy","xnk","bez","seth","bee","instar","tube","ledu","hqx","stac","j8t","chp","tomo","grft","bax","elec","btcp","tfd","ship","ldc","shp","lala","occ","deb","cennz","snx","fluz","getx","drop","banca","drg","latx","nanj","ckusd","up","bbn","noah","lgo","1wo","npx","bitg","amlt","lst","pcl","sig","rntb","xbp","lnc","spd","ipsx","scc","bstn","swth","sen","xclr","senc","vit","tpay","berry","xtc","nct","xsn","xdce","tds","sent","adh","snip","bsm","dev","cbt","grmd","auc","bubo","dan","mfg","dig","adi","xhv","kst","crc","dero","efx","ftx","earth","mrk","pxc","crop","srcoin","chx","msr","phi","bbc","dml","hbz","ori","trak","aph","lnd","xes","vips","rblx","btrn","nbai","vme","hydro","ss","cel","ttt","bci","betr","amn","flp","cmct","mitx","mtc","cjt","bouts","pal","cre","gene","apr","ac3","fxt","zipt","gen","bznt","lif","team","oot","atx","frec","dgx","skb","joint","grn","bmh","loki","sgn","fnd","dtrc","cln","hero","clo","ubt","pat","open","mrph","sntr","xyo","cpt","apis","ft","xrh","caz","red","dgtx","gin","inv","face","ava","luc","navi","soul","repo","ivy","cnet","bbo","0xbtc","pi","lyl","bnk","etz","omx","medx","fto","abyss","pmnt","hur","tmt","pkc","cbc","ceek","sal","cou","hold","trtt","upp","bwt","dag","etk","met","aoa","alx","tern","zcn","zinc","fsbt","car","bob","kndc","card","wwb","onl","otb","coni","cccx","got","thrt","pch","quro","ess","hsc","like","yup","xsg","dtx","moc","nim","bz","dws","zxc","olt","atmi","xmct","fnkos","susd","tgame","engt","nobs","bmx","cdm","gard","xd","spx","aeg","rpl","ely","sop","jot","krl","lemo","gbc","bwx","wys","cosm","nrve","trtl","wt","toto","rlx","chex","view","vikky","foxt","brdg","lcs","zpr","empr","lpc","fundz","ryo","aced","lfc","wab","csm","mvl","xxx","ncp","dacc","pgn","eurs","exmr","nix","apl","bws","dpn","vex","hdac","kwh","mct","wsd","acdc","nbr","hrc","vivid","purex","cen","bitx","priv","rmesh","bbk","coin","klks","bhp","incx","zmn","sem","aro","acre","iov","web","fmf","zel","bnn","obt","exc","czr","pts","opcx","bnd","xun","btk","dtem","eli","dacs","ebc","esco","gmcn","vitae","rock2","xtrd","btcn","nam","euno","mgd","est","eds","kind","x8x","cmm","ecom","lina","ino","knt","croat","btcone","avinoc","wiki","spn","nug","scr","nbc","npxsxem","xov","ust","opti","gic","abdt","pkg","boc","rdc","newos","pdx","xpat","icr","mxm","gio","sds","own","ig","hth","gse","dgs","xdna","xpx","nyex","tic","egem","arepa","xet","cedex","noku","dx","pass","baas","pco","thr","cyfm","metm","aka","obtc","tkt","qnt","sac","zcr","xap","ifp","svd","ylc","mero","pma","arion","xbi","ftt","hyb","hb","lgs","fntb","ttc","altx","seal","lky","abx","hand","gpkr","zp","ect","mftu","cato","rrc","rating","ctc","know","grph","kxc","nsd","prtx","lobs","vdg","sat","yuki","kwatt","mib","cotn","soniq","gtm","delta","nrg","ftxt","dav","bnc","dow","qbit","btn","vulc","str","flm","tmc","at","esn","fkx","beet","mic","ubc","flot","use","zba","mri","cymt","btr","gze","ccl","din","dit","havy","care","prj","cif","echt","imp","c2p","c8","sno","vsc","peng","rth","ret","qntu","tv","foin","bir","daxt","ben","elli","btxc","cit","olmp","btad","bu","urals","ihf","ucn","molk","edn","spnd","xcg","ccc","alc","cstl","evi","cfl","boxx","aog","ctrt","tcn","bunny","pyn","plura","rox","che","six","cmit","wiz","goss","sol","xcash","shard","iqn","qch","paxex","pgts","mlc","phon","anon","ecoreal","daps","carat","mnp","pyx","dachx","zb","mas","well","trxc","azart","tmtg","dagt","wit","ert","aux","wxc","plc","vsf","sins","crd","kue","ner","mir","rpm","ragna","dec","xgs","labh","wbl","civ","bcard","benz","xg","acm","blast","tol","quan","fox","mash","our","steep","nrp","scriv","wsp","x12","shade","ifood","egx","wix","pndm","bc","eta","bsx","wtl","sim","ndx","zeus","bczero","wage","f1c","meta","qac","cobra","psc","shping","s","qno","aec","inco","aglt","icnq","rpd","ents","smq","oxy","deal","wire","divi","xind","huzu","kun","znt","ath","mmo","szc","etho","cjs","datp","deex","plus1","ird","zt","help","rpi","cheese","alt","isr","hlm","fbn","tdp","robet","jse","yeed","itl","asa","modx","shmn","pny","telos","omen","gst","ddx","pedi","gzro","esce","vlu","ezw","vld","sake","rock","mtrc","bzx","xcd","lqd","deliz","cgen","hndc","type","ionc","mbc","bzl","voco","traid","apc","sin","dex","dkpc","snr","brzc","paws","messe","snd","mfit","apot","cyl","pnk","medibit","poss","ttv","wet","bgg","abs","ethm","cryp","lion","ptn","xnv","inve","osa","eti","hum","shb","vites","vest","cwv","micro","nor","bcac","dashg","hqt","bcdt","stacs","beat","btnt","nos","oio","nzl","equad","bltg","lrm","herc","foam","opq","lml","aergo","skch","lpt","tiox","tvnt","shvr","herb","cnus","nplc","next","b2n","brc","fiii","becn","quin","shx","xfc","agvc","impl","awc","prx","wco","rom","kze","dogec","btmx","gbt","roco","ibt","cent","xta","aen","b2g","btcl","mox","mcw","bul","tok","m2o","inx","hyn","xsm","gmbc","obsr","ctx","rif","adm","vsys","dxr","alb","tosc","exo","pla","clb","wib","lto","caj","veo","wbtc","sdusd","temco","solve","dooh","flc","halo","wlo","tcat","ccx","s4f","elac","vgw","mrs","btu","dcto","const","blcr","ecte","bnana","qusd","webn","777","eld","aunit","hxro","ichx","xpc","labx","upx","pltc","evy","mhc","gmb","mpg","jnb","swc","1sg","owc","set","serv","fat","1x2","pib","hbx","ccn","etx","twins","gfun","spt","evos","cot","ankr","ovc","lunes","innbcl","net","boltt","rc20","jwl","araw","gali","zeon","mesg","xbx","xuez","fex","bora","ctt","dra","spec","navy","orox","dos","etgp","ine","fxc","pton","vra","slrm","gpt","bhig","xqr","olxa","pub","bud","jct","nex","veil","sha","bbgc","hypx","dxg","orbs","mfc","hlt","xrc","fst","xlb","csp","bolt","xtx","vidt","vbk","obx","when","oto","alln","huddl","mtv","und","locus","sfcp","gfr","fnb","pti","inf","uts","con","sica","a","lit","bia","botx","valor","entrc","webd","xwp","esbc","oce","stash","arq","qcx","fx","wpp","ict","bceo","nat","now","cspn","mac","axe","hn","kubo","xmv","e2c","bitc","dio","spdr","peos","ocean","wgp","ttn","meri","thx","sntvt","doget","dpt","hlx","tas","vjc","drep","trat","xlmg","atls","idex","bqtx","elet","xcon","swift","cnns","srk","gny","nnb","mzk","trp","p2px","fab","qwc","neox","tera","afin","ntr","arrr","iotw","eved","odex","bomb","rfox","neal","bze","vdx","dream","toc","bhd","eum","try","grat","tro","aya","btc2","snl","chr","tcash","tqn","lbn","ogo","bdx","lvl","wfx","alp","coti","emt","smartup","ioux","bst","kri","tagz","mpay","skt","znn","stpt","bcz","izi","sprk","bqqq","minx","mcpc","eosdt","kts","usdq","raven","dvt","jar","hnst","cos","mbl","nbot","mgc","par","qdao","igg"];

    public function test($symbol)
    {
        if (time() - strtotime(date('Y-m-d 07:59:59')) > 0) {
            $size = intval((time() - strtotime(date('Y-m-d 07:00:00'))) / 3600);
        } else {
            $size = intval((time() -strtotime(date('Y-m-d 07:00:00',strtotime('-1 day')))) / 3600);
        }

        return $size;
    }

    public function getPrice($symbol)
    {
        $count = 0;
        $price = 0;

        if ($lbankPrice = $this->__getPriceFromLbank($symbol)) {
            $count ++;
            $price += $lbankPrice;
        }

        if ($binancePrice = $this->__getPriceFromBinance($symbol)) {
            $count ++;
            $price += $binancePrice;
        }

        if ($huoBiPrice = $this->__getPriceFromHuoBi($symbol)) {
            $count ++;
            $price += $huoBiPrice;
        }

        if ($cointigerPrice = $this->__getPriceFromCointiger($symbol)) {
            $count ++;
            $price += $cointigerPrice;
        }

        if ($okexPrice = $this->__getPriceFromOkex($symbol)) {
            $count ++;
            $price += $okexPrice;
        }

        $cou = $count > 0  ? $count : 1;
        return round($price / $cou, 8);
    }

    private function __getPriceFromBinance($symbol, $is_check = true)
    {
        try {
            $symbol = strtoupper($symbol);
            $basePrice = 1;
            if ($is_check) {
                if (!DataCache::getSymbols('symbol_binance_ETH_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_binance_BTC_' . $symbol)) {
                        if (!DataCache::getSymbols('symbol_binance_USDT_' . $symbol)) {
                            if (!DataCache::getSymbols('symbol_binance_BNB_' . $symbol)) {
                                return 0;
                            } else {
                                $symbol .= 'BNB';
                                $basePrice = $this->__getBasePrice('bnb');
                            }
                        }else {
                            $symbol .= 'USDT';
                        }
                    } else {
                        $symbol .= 'BTC';
                        $basePrice = $this->__getBasePrice('btc');
                    }
                } else {
                    $symbol .= 'ETH';
                    $basePrice = $this->__getBasePrice('eth');
                }
            }

            \Log::info('binance price symbol = '. $symbol);
            $url = 'https://api.binance.com/api/v3/ticker/price?symbol=';
            $client = new Client();
            $res = $client->request('GET', $url . $symbol);
            $resData  = json_decode((string) $res->getBody());

            return isset($resData->price) ? $resData->price * $basePrice : 0;
        } catch (ConnectException $e) {
            \Log::error($e->getMessage());
            return 0;
        }
    }

    private function __getBasePrice($symbol)
    {
        if ($price = DataCache::getBaseSymbolPrice($symbol)) {
            return $price;
        }

        switch ($symbol) {
            case 'eth':
                $price = $this->__getPriceFromBinance("ETHUSDT", false);
                break;
            case 'btc':
                $price = $this->__getPriceFromBinance("BTCUSDT", false);
                break;
            case 'bnb':
                $price = $this->__getPriceFromBinance("BNBUSDT", false);
                break;
            case 'ht':
                $price = $this->__getPriceFromHuoBi("htusdt", false);
                break;
            case "okb":
                $price = $this->__getPriceFromOkex("OKB-USDT", false);
                break;
        }

        DataCache::setBaseSymbolsPrice($symbol, $price);
        return $price;
    }

    private function __getPriceFromHuoBi($symbol, $is_check = true)
    {
        try{
            $basePrice = 1;
            if ($is_check) {
                if (!DataCache::getSymbols('symbol_huobi_eth_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_huobi_btc_' . $symbol)) {
                        if (!DataCache::getSymbols('symbol_huobi_usdt_' . $symbol)) {
                            if (!DataCache::getSymbols('symbol_huobi_ht_' . $symbol)) {
                                return 0;
                            } else {
                                $symbol .= 'ht';
                                $basePrice = $this->__getBasePrice('ht');
                            }
                        }else {
                            $symbol .= 'usdt';
                        }
                    } else {
                        $symbol .= 'btc';
                        $basePrice = $this->__getBasePrice('btc');
                    }
                } else {
                    $symbol .= 'eth';
                    $basePrice = $this->__getBasePrice('eth');
                }
            }

            \Log::info('huobi price symbol = '. $symbol);

            if ($cache = DataCache::getSymbolInfo('symbol-info-huobi-' . $symbol)) {
                return isset($cache['tick']['data']) ? $cache['tick']['data'][0]['price'] * $basePrice : 0;
            }
            $url='https://api.huobi.pro/market/trade?symbol=';
            $client = new Client();
            $res = $client->request('GET', $url . $symbol);
            $resData  = json_decode((string) $res->getBody());

            if ($resData->status == 'ok') {
                DataCache::setSymbolInfo('symbol-info-huobi-' . $symbol, $resData);
                return isset($resData->tick) && isset($resData->tick->data) ? $resData->tick->data[0]->price * $basePrice : 0;
            } else {
                return 0;
            }
            return isset($resData->price) ? $resData->price : 0;
        } catch (ConnectException $e) {
            \Log::error($e->getMessage());
            return 0;
        }
    }

    private function __getPriceFromCointiger($symbol)
    {
        try{
            $basePrice = 1;
            if (!DataCache::getSymbols('symbol_cointiger_eth_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_cointiger_btc_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_cointiger_usdt_' . $symbol)) {
                        return 0;
                    }else {
                        $symbol .= 'usdt';
                    }
                } else {
                    $symbol .= 'btc';
                    $basePrice = $this->__getBasePrice('btc');
                }
            } else {
                $symbol .= 'eth';
                $basePrice = $this->__getBasePrice('eth');
            }

            \Log::info('cointiger price symbol = '. $symbol);

            if ($cache = DataCache::getSymbolInfo('symbol-info-cointiger-' . $symbol)) {
                return isset($cache['data']['trade_data']) ? $cache['data']['trade_data'][0]['price'] * $basePrice : 0;
            }

            $url = 'https://api.cointiger.com/exchange/trading/api/market/history/trade?symbol=';
            $client = new Client();
            $res = $client->request('GET', $url . $symbol);
            $resData  = json_decode((string) $res->getBody());

            if ($resData->code == '0') {
                //cointiger 需要算出来涨跌幅 而这个接口给了 最新价格 所以需要缓存一下 方便下面__getDetailOfCointiger 用
                DataCache::setSymbolInfo('symbol-info-cointiger-' . $symbol, $resData);
                return isset($resData->data) && isset($resData->data->trade_data) ? $resData->data->trade_data[0]->price * $basePrice : 0;
            } else {
                return 0;
            }
        } catch (ConnectException $e) {
            \Log::error($e->getMessage());
            return 0;
        }
    }

    private function __getPriceFromLbank($symbol, $switch = 3)
    {
        try{
            $basePrice = 1;
            if (!DataCache::getSymbols('symbol_lbank_eth_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_lbank_btc_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_lbank_usdt_' . $symbol)) {
                        return 0;
                    }else {
                        $symbol .= '_usdt';
                    }
                } else {
                    $symbol .= '_btc';
                    $basePrice = $this->__getBasePrice('btc');
                }
            } else {
                $symbol .= '_eth';
                $basePrice = $this->__getBasePrice('eth');
            }
           \Log::info('lbank price symbol = '. $symbol);

            if ($cache = DataCache::getSymbolInfo('symbol-info-lbank-' . $symbol)) {
                return isset($cache['ticker']['latest']) ? $cache['ticker']['latest'] * $basePrice : 0;
            }

            $url = 'https://www.lbkex.net/v1/ticker.do?symbol=';
            $client = new Client();
            $res = $client->request('GET', $url . $symbol);
            $resData  = json_decode((string) $res->getBody());

            //Lbank 需要算出来涨跌幅 而这个接口给了 最新价格 所以需要缓存一下 方便下面__getDetailOfLbank 用
            DataCache::setSymbolInfo('symbol-info-lbank-' . $symbol, $resData);

            if (isset($resData->result) && $resData->result === 'false') {
                return 0;
            }

            return isset($resData->ticker->latest) ? $resData->ticker->latest * $basePrice : 0;
        } catch (ConnectException $e) {
            \Log::error($e->getMessage());
            $price = 0;
            if ($switch) {
                sleep(2);
                $price = $this->__getPriceFromLbank($symbol, --$switch);
            }
            return $price;
        }
    }

    private function __getPriceFromOkex($symbol, $is_check = true)
    {
        try{
            $basePrice = 1;
            $symbol = strtoupper($symbol);

            if ($is_check) {
                if (!DataCache::getSymbols('symbol_okex_ETH_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_okex_BTC_' . $symbol)) {
                        if (!DataCache::getSymbols('symbol_okex_USDT_' . $symbol)) {
                            if (!DataCache::getSymbols('symbol_okex_OKB_' . $symbol)) {
                                return 0;
                            } else {
                                $symbol .= '-OKB';
                                $basePrice = $this->__getBasePrice('okb');
                            }
                        }else {
                            $symbol .= '-USDT';
                        }
                    } else {
                        $symbol .= '-BTC';
                        $basePrice = $this->__getBasePrice('btc');
                    }
                } else {
                    $symbol .= '-ETH';
                    $basePrice = $this->__getBasePrice('eth');
                }
            }

            \Log::info('okex price symbol = '. $symbol);
            if ($cache = DataCache::getSymbolInfo('symbol-info-okex-' . $symbol)) {
                return isset($cache['last']) ? $cache['last'] * $basePrice : 0;
            }

            $url = 'https://www.okex.com/api/spot/v3/instruments/' . $symbol . '/ticker';
            $client = new Client();
            $res = $client->request('GET', $url);
            $resData  = json_decode((string) $res->getBody());

            //由于okex 需要算出来涨跌幅 而这个接口给了 最新价格 所以需要缓存一下 方便下面__getDetailOfOkex 用
            DataCache::setSymbolInfo('symbol-info-okex-' . $symbol, $resData);

            return isset($resData->last) ? $resData->last * $basePrice : 0;
         } catch (ConnectException $e) {
            \Log::error($e->getMessage());

            return 0;
        }
    }

    public function get24DetailFor($symbol)
    {
\Log::info('=======================================================');
        $count = 0;
        $rose = 0;
        if ($lbankDetail = $this->__getDetailOfLbank($symbol)) {
            \Log::info('lbank----------------------->' . $lbankDetail);
            $count ++;
            $rose += $lbankDetail;
        }

        if ($binanceDetail = $this->__getDetailOfbinance($symbol)) {
            \Log::info('binance----------------------->' . $binanceDetail);
            $count ++;
            $rose += $binanceDetail;
        }

        if ($huoBiDetail = $this->__getDetailOfHuobi($symbol)) {
            \Log::info('huobi----------------------->' . $huoBiDetail);
            $count ++;
            $rose += $huoBiDetail;
        }

        if ($cointigerDetail = $this->__getDetailOfCointiger($symbol)) {
            \Log::info('cointiger----------------------->' . $cointigerDetail);
            $count ++;
            $rose += $cointigerDetail;
        }

        if ($okexDetail = $this->__getDetailOfOkex($symbol)) {
            \Log::info('okex------------------------>' . $okexDetail);
            $count ++;
            $rose += $okexDetail;
        }
\Log::info('******************************************************');
        $cou = $count > 0  ? $count : 1;

        return round($rose / $cou, 4);
    }

    private function __getDetailOfCointiger($symbol)
    {
        try{
            if (!DataCache::getSymbols('symbol_cointiger_eth_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_cointiger_btc_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_cointiger_usdt_' . $symbol)) {
                        return 0;
                    }else {
                        $symbol .= 'usdt';
                    }
                } else {
                    $symbol .= 'btc';
                }
            } else {
                $symbol .= 'eth';
            }
            $cache = DataCache::getSymbolInfo('symbol-info-cointiger-' . $symbol);

            if (isset($cache['code']) && $cache['code'] != '0') {
                return 0;
            }

            $lastPrice = $cache['data']['trade_data'][0]['price'];

            if($yesterdaylastPrice = DataCache::getSymbolYesterdayLastPrice("cointiger-". $symbol)){
                \Log::info('cointiger rose cache symbol = '. $symbol);
                return ($lastPrice - $yesterdaylastPrice) / $yesterdaylastPrice * 100;
            } else {
                //获取最近一条7点钟的数据收盘价 作为基准, UTC + 8 是当前时区
                if (time() - strtotime(date('Y-m-d 07:59:59')) > 0) {
                    $size = intval((time() - strtotime(date('Y-m-d 07:00:00'))) / 3600);
                } else {
                    $size = intval((time() -strtotime(date('Y-m-d 07:00:00',strtotime('-1 day')))) / 3600);
                }
                $url = 'https://api.cointiger.com/exchange/trading/api/market/history/kline?symbol=' . $symbol . '&period=60min&size=' . $size;
                $client = new Client();
                $res = $client->request('GET', $url);
                $resData  = json_decode((string) $res->getBody(), true);

                if ($resData['code'] == '0') {
                    $yesterdaylastPrice = $resData['data']['kline_data'][0]['close'];

                    \Log::info('cointiger rose symbol = '. $symbol);
                    DataCache::setSymbolYesterdayLastPrice("cointiger-". $symbol, $yesterdaylastPrice);

                    return ($lastPrice - $yesterdaylastPrice) / $yesterdaylastPrice * 100;
                } else {
                    return 0;
                }
            }
        } catch (ConnectException $e) {
            \Log::error($e->getMessage());
            return 0;
        }
    }
    private function __getDetailOfbinance($symbol)
    {
        try{
            $symbol = strtoupper($symbol);
            if (!DataCache::getSymbols('symbol_binance_ETH_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_binance_BTC_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_binance_USDT_' . $symbol)) {
                        if (!DataCache::getSymbols('symbol_binance_BNB_' . $symbol)) {
                            return 0;
                        } else {
                            $symbol .= 'BNB';
                        }
                    }else {
                        $symbol .= 'USDT';
                    }
                } else {
                    $symbol .= 'BTC';
                }
            } else {
                $symbol .= 'ETH';
            }

            \Log::info('binance rose symbol = '. $symbol);
            $url = 'https://api.binance.com/api/v1/ticker/24hr?symbol=';
            $client = new Client();
            $res = $client->request('GET', $url . $symbol);
            $resData  = json_decode((string) $res->getBody());

            return isset($resData->priceChangePercent) ?  $resData->priceChangePercent : 0;
        } catch (ConnectException $e) {
            \Log::error($e->getMessage());
            return 0;
        }
    }

    private function __getDetailOfHuobi($symbol)
    {
        try{
            if (!DataCache::getSymbols('symbol_huobi_eth_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_huobi_btc_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_huobi_usdt_' . $symbol)) {
                        if (!DataCache::getSymbols('symbol_huobi_ht_' . $symbol)) {
                            return 0;
                        } else {
                            $symbol .= 'ht';
                        }
                    }else {
                        $symbol .= 'usdt';
                    }
                } else {
                    $symbol .= 'btc';
                }
            } else {
                $symbol .= 'eth';
            }

            $cache = DataCache::getSymbolInfo('symbol-info-huobi-' . $symbol);

            if (isset($cache['status']) && $cache['status'] != 'ok') {
                return 0;
            }

            $lastPrice = $cache['tick']['data'][0]['price'];

            if($yesterdaylastPrice = DataCache::getSymbolYesterdayLastPrice("huobi-". $symbol)){
                \Log::info('huobi rose cache symbol = '. $symbol);
                return ($lastPrice - $yesterdaylastPrice) / $yesterdaylastPrice * 100;
            } else {
                //获取最近一条7点钟的数据收盘价 作为基准, UTC + 8 是当前时区
                if (time() - strtotime(date('Y-m-d 07:59:59')) > 0) {
                    $size = intval((time() - strtotime(date('Y-m-d 07:00:00'))) / 3600);
                } else {
                    $size = intval((time() -strtotime(date('Y-m-d 07:00:00',strtotime('-1 day')))) / 3600);
                }

                $url='https://api.huobi.com/market/history/kline?symbol=' . $symbol . '&period=60min&size=' . $size;

                $client = new Client();
                $res = $client->request('GET', $url);
                $resData  = json_decode((string) $res->getBody(), true);

                if ($resData->status == 'ok') {
                    $yesterdaylastPrice = $resData['data'][$size - 1]['close'];
                    \Log::info('huobi rose symbol = '. $symbol);
                    DataCache::setSymbolYesterdayLastPrice("huobi-". $symbol, $yesterdaylastPrice);

                    return ($lastPrice - $yesterdaylastPrice) / $yesterdaylastPrice * 100;
                } else {
                    return 0;
                }
            }
        } catch (ConnectException $e) {
            \Log::error($e->getMessage());
            return 0;
        }
    }

    private function __getDetailOfLbank($symbol, $switch = 3)
    {
        try{
            if (!DataCache::getSymbols('symbol_lbank_eth_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_lbank_btc_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_lbank_usdt_' . $symbol)) {
                        return 0;
                    }else {
                        $symbol .= '_usdt';
                    }
                } else {
                    $symbol .= '_btc';
                }
            } else {
                $symbol .= '_eth';
            }

            $cache = DataCache::getSymbolInfo('symbol-info-lbank-' . $symbol);

            if (isset($cache['result']) && $cache['result'] === 'false') {
                return 0;
            }

            $lastPrice = $cache['ticker']['latest'];

            if($yesterdaylastPrice = DataCache::getSymbolYesterdayLastPrice("lbank-". $symbol)){
                \Log::info('lbank rose cache symbol = '. $symbol);
                return ($lastPrice - $yesterdaylastPrice) / $yesterdaylastPrice * 100;
            } else {
                if (time() - strtotime(date('Y-m-d 07:59:59')) > 0) {
                    $time = strtotime(date('Y-m-d 07:00:00'));
                } else {
                    $time = strtotime(date('Y-m-d 07:00:00',strtotime('-1 day')));
                }
                $url = 'https://www.lbkex.net/v1/kline.do?symbol='. $symbol .'&size=1&type=hour1&time=' . $time;

                $client = new Client();
                $res = $client->request('GET', $url);
                $resData  = json_decode((string) $res->getBody(), true);

                \Log::info('lbank rose symbol = '. $symbol);
                DataCache::setSymbolYesterdayLastPrice("lbank-". $symbol, $resData[0][4]);

                return isset($resData[0][4]) ?  ($lastPrice - $resData[0][4]) / $resData[0][4] * 100  : 0;
            }
        } catch (ConnectException $e) {
            \Log::error($e->getMessage());

            $rose = 0;
            if ($switch) {
                sleep(2);
                $rose = $this->__getDetailOfLbank($symbol, --$switch);
            }
            return $rose;
        }
    }

    private function __getDetailOfOkex($symbol)
    {
        $symbol = strtoupper($symbol);

        if (!DataCache::getSymbols('symbol_okex_ETH_' . $symbol)) {
            if (!DataCache::getSymbols('symbol_okex_BTC_' . $symbol)) {
                if (!DataCache::getSymbols('symbol_okex_USDT_' . $symbol)) {
                    if (!DataCache::getSymbols('symbol_okex_OKB_' . $symbol)) {
                        return 0;
                    } else {
                        $symbol .= '-OKB';
                    }
                } else {
                    $symbol .= '-USDT';
                }
            } else {
                $symbol .= '-BTC';
            }
        } else {
            $symbol .= '-ETH';
        }

        $cache = DataCache::getSymbolInfo('symbol-info-okex-' . $symbol);
        $lastPrice = $cache['last'];

        if($yesterdaylastPrice = DataCache::getSymbolYesterdayLastPrice("okex-". $symbol)){
            \Log::info('okex rose cache symbol = '. $symbol);
            return ($lastPrice - $yesterdaylastPrice) / $yesterdaylastPrice * 100;
        } else {
            $date = date('Y-m-d', strtotime('-1 day'));
            $url = 'https://www.okex.com/api/spot/v3/instruments/' . $symbol . '/candles?granularity=3600&start='. $date .'T00%3A00%3A00.000Z&end=' . $date . 'T23%3A59%3A59.999Z';
            $client = new Client();
            $res = $client->request('GET', $url);
            $resData  = json_decode((string) $res->getBody(), true);
            \Log::info('okex rose symbol = '. $symbol);
            DataCache::setSymbolYesterdayLastPrice("okex-". $symbol, $resData[0][4]);

            return isset($resData[0][4]) ? ($lastPrice - $resData[0][4]) / $resData[0][4] * 100 : 0;
        }
    }



    public function wechatMessageCallback(Request $request)
    {
        $appid = $request->get('appid');
        $timestamp = $request->get('timestamp');
        $data = $request->get('data');
        $sign = $request->get('sign');


        $key = '47886fd0de1asdf135sq22fy56w2kl';
        $secret = '1equEcRkT2hirJhbYByNGCZPRHgFg132rtlb0IZ3vf4=';

        $checkSign = md5($appid . $secret . $timestamp . $data);

        if ($sign != $checkSign) {
           return response()->json([
                'resultcode' => -11001,
                'resultdesc' => 'invalid credentials',
                'data' => null,
           ], 200);
        }

        $data = json_decode(base64_decode($data));
        $symbol = strtolower($data->vcKeyword);

        if (in_array($symbol, static::OTHER_SYMBOL)) {
            return response()->json([
                'resultcode' => 0,
                'resultdesc' => 'success',
                'data' => [
                    'nMsgType' => 2001,
                    'vcContent' => '币海茫茫，韭妹儿能力有限，寻它不着~
添加币种，请加好友 xiaoyu238491
[' . date('Y-m-d H:i:s') .  ']
http://8i1.cn/ujj',
                    'vcShareTitle' => null,
                    'vcShareDesc' => null,
                    'vcShareUrl' => null,
                ],
           ], 200);
        }

        DataCache::callTotal();
        DataCache::zincrOfScoreFor($symbol, 1);

        if ($d = DataCache::getSymbolInfo('symbol-info-data-' . $symbol)) {
            $price = $d['price'];
            $rose = $d['rose'];
        } else {
            if (!DataCache::lock('symbol-info-lock', 1)) {
               sleep(1);
            }
            $p = $this->getPrice($symbol);
            $price = $p >= 1 ? round($p, 5) : round($p, 6);
            $rose = round($this->get24DetailFor($symbol), 2);
            DataCache::setSymbolInfo('symbol-info-data-' . $symbol, ['price' => $price, 'rose' => $rose]);
        }
        $cny = DataCache::getCurrency('cny');
        $tip = $this->__getTip($symbol, $rose);
        return response()->json([
                'resultcode' => 0,
                'resultdesc' => 'success',
                'data' => [
                    'nMsgType' => 2001,
                    'vcContent' => '币种: ' . strtoupper($data->vcKeyword) .'
币价: ¥' . round($price * $cny, 5) .' / $' . $price . '
涨跌幅:
24H: ' . ($rose > 0 ?('+' . $rose . '% ↑') : $rose . '% ↓' ) . ($tip ? '\n' . $tip : '' ) .'
[' . date('Y-m-d H:i:s') .  ']
http://8i1.cn/ujj',
                    'vcShareTitle' => null,
                    'vcShareDesc' => null,
                    'vcShareUrl' => null,

                ],
           ], 200);
    }

    private function __getTip($symbol, $rose)
    {
        if (in_array($symbol, static::TIP_SYMBLOS)) {
            if ($rose > 20) {
                return static::TIPS[$symbol]['20'];
            }

            if ($rose > 10) {
                return static::TIPS[$symbol]['10'];
            }

            if ($rose > 50) {
                return static::TIPS[$symbol]['5'];
            }

            if ($rose > 2) {
                return static::TIPS[$symbol]['2'];
            }

            return null;
        } else {
            if ($rose > 200) {
                return static::TIPS['other']['200'];
            }

            if ($rose > 100) {
                return static::TIPS['other']['100'];
            }

            if ($rose > 50) {
                return static::TIPS['other']['50'];
            }

            if ($rose > 20) {
                return static::TIPS['other']['20'];
            }

            if ($rose > 10) {
                return static::TIPS['other']['10'];
            }

            return null;
        }
    }

    public function setKeyword()
    {
        return '请确认----, 然后在代码里删除此行';
        $symbols = DataCache::getSymbols('keywords-symbol');

        $data = base64_encode(json_encode([
            'nActivityId' => 10013,
            'vcName' => 10013,
            'vcKeyword' => $symbols
        ]));

        $timestamp = time();

        $sign = md5(static::JIA_QUN_LA_APPID . static::JIA_QUN_LA_SECRET . $timestamp . $data);

        $url = 'http://xzscallback.jinqunla.com/api/External/keyword/SetkeywordsImport';
        $body =  json_encode([
                   'appid' => static::JIA_QUN_LA_APPID,
                   'sign' => $sign,
                   'timestamp' => $timestamp,
                   'data' => $data,
               ]);

        $client = new Client();
        $res = $client->request('POST', $url,  [
            'body' => $body,
            'headers' => ['Content-Type' => 'application/json']
           ]);
        $resData  = json_decode((string) $res->getBody(), true);
        return $resData;
    }

    public function getStatistic()
    {
        $total = DataCache::getAllSymbolCount();
        $detail = DataCache::getSymbolCountDetail();

        return ['total' => $total, 'detail' => $detail];
    }
}