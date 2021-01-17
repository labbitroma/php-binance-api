<?php

/*
 * ============================================================
 * @package php-binance-api
 * @link https://github.com/jaggedsoft/php-binance-api
 * @subpackage php-binance-futures
 * @link https://github.com/labbitroma/php-binance-futures
 * ============================================================
 * @author Niccolò Venturoli
 * @company Labbit srl
 * @license MIT License
 * ============================================================
 * A curl HTTP REST wrapper for the binance futures API
 */
namespace Binance;

// PHP version check
if (version_compare(phpversion(), '7.0', '<=')) {
    fwrite(STDERR, "Hi, PHP " . phpversion() . " support will be removed very soon as part of continued development.\n");
    fwrite(STDERR, "Please consider upgrading.\n");
}

require_once(dirname(__FILE__) . "/php-binance-api.php");

class FAPI extends API
{
    protected $base = 'https://fapi.binance.com/fapi/'; 
    protected $endpoints = [
        'trades' => 'v1/trades',
        'historicalTrades' => 'v1/historicalTrades',
        'premiumIndex' => 'v1/premiumIndex',
        'indexInfo' => 'v1/indexInfo',
        'fundingRate' => 'v1/fundingRate',
        'allForceOrders' => 'v1/allForceOrders',
        'openInterest' => 'v1/openInterest',
        'openInterestHist' => '/futures/data/openInterestHist',
        'topLongShortAccountRatio' => '/futures/data/topLongShortAccountRatio',
        'topLongShortPositionRatio' => '/futures/data/topLongShortAccountRatio',
        'globalLongShortAccountRatio' => '/futures/data/topLongShortAccountRatio',
        'takerlongshortRatio' => '/futures/data/topLongShortAccountRatio',
        
        'order'	=> 'v1/order',
        'order/test'	=> 'v1/order/test',
        'openOrders'	=> 'v1/openOrders',
        'allOrders'	=> 'v1/allOrders',
        'myTrades'	=> null,
        'time'	=> 'v1/time',
        'exchangeInfo'	=> 'v1/exchangeInfo',
        'withdraw'	=> null,
        'depositAddress' => null,
        'depositHistory' => null,
        'withdrawHistory' => null,
        'withdrawFee'	=> null,
        'ticker/price'	=> 'v1/ticker/price',
        'ticker/bookTicker' => 'v1/ticker/bookTicker',
        'ticker/24hr'	=> 'v1/ticker/24hr',
        'aggTrades'	=> 'v1/aggTrades',
        'depth'	=> 'v1/depth',
        'account'	=> 'v2/balance',
        'klines'	=> 'v1/klines',
        'userDataStream' => null
    ];

    public function __construct() {
        parent::__construct(...func_get_args());
    }
    
   public function recentTrades(string $symbol) {
       return $this->httpRequest($this->endpoints['trades'], "GET", [ 'symbol' => $symbol ]);
   }
   
   public function historicalTrades(string $symbol, int $limit = 500, int $fromTradeId = -1) {
       $opt = [
            "symbol" => $symbol,
            "limit" => $limit,
        ];
        if ($fromTradeId > 0) {
            $opt["fromId"] = $fromTradeId;
        }
        return $this->httpRequest($this->endpoints['historicalTrades'], "GET", $opt);
   }

   public function markPrice(string $symbol) {
       return $this->httpRequest($this->endpoints['premiumIndex'], "GET", [ 'symbol' => $symbol ]);
   }
   
   public function indexInfo(string $symbol) {
       return $this->httpRequest($this->endpoints['indexInfo'], "GET", [ 'symbol' => $symbol ]);
   }
   
   public function fundingRates(string $symbol, int $limit = null, $startTime = null, $endTime = null) {
        $opt = [
            "symbol" => $symbol
        ];
        if ($limit) {
            $opt["limit"] = $limit;
        }
        if ($startTime) {
            $opt["startTime"] = $startTime;
        }
        if ($endTime) {
            $opt["endTime"] = $endTime;
        }
        return $this->httpRequest($this->endpoints['fundingRate'], "GET", $opt);
    }
    
    public function liquidationOrders(string $symbol, int $limit = 100, $startTime = null, $endTime = null) {
        $opt = [ "symbol" => $symbol ];
        if ($limit) {
            $opt["limit"] = $limit;
        }
        if ($startTime) {
            $opt["startTime"] = $startTime;
        }
        if ($endTime) {
            $opt["endTime"] = $endTime;
        }
        return $this->httpRequest($this->endpoints['allForceOrders'], "GET", $opt);
    }
    
    public function openInterest(string $symbol) {
        return $this->httpRequest($this->endpoints['openInterest'], "GET", [ "symbol" => $symbol ]);
    }
    
    public function openInterestHistorical(string $symbol, string $interval = "5m", int $limit = null, $startTime = null, $endTime = null) {
        $opt = [
            "symbol" => $symbol,
            "period" => $interval,
        ];
        if ($limit) {
            $opt["limit"] = $limit;
        }
        if ($startTime) {
            $opt["startTime"] = $startTime;
        }
        if ($endTime) {
            $opt["endTime"] = $endTime;
        }
        return $this->httpRequest($this->endpoints['openInterestHist'], "GET", $opt);
    }
    
    public function longshortRatio(string $type, string $symbol, string $interval = "5m", int $limit = null, $startTime = null, $endTime = null) {
        $opt = [
            "symbol" => $symbol,
            "period" => $interval,
        ];
        if ($limit) {
            $opt["limit"] = $limit;
        }
        if ($startTime) {
            $opt["startTime"] = $startTime;
        }
        if ($endTime) {
            $opt["endTime"] = $endTime;
        }
        $endpoint = '';
        switch ($type) {
            case 'accounts':
                $endpoint = 'topLongShortAccountRatio';
                break;
            case 'positions':
                $endpoint = 'topLongShortPositionRatio';
                break;
            case 'global':
                $endpoint = 'globalLongShortAccountRatio';
                break;
            case 'taker':
                $endpoint = 'takerlongshortRatio';
                break;
        }
        return $this->httpRequest($this->endpoints[$endpoint], "GET", $opt);
    }
    
    public function order(string $side, string $symbol, $quantity, $price, string $type = "LIMIT", array $flags = [], bool $test = false) {
        $opt = array_merge([
            "symbol" => $symbol,
            "side" => $side,
            "type" => $type,
            "recvWindow" => 60000,
        ], $flags);

        // someone has preformated there 8 decimal point double already
        // dont do anything, leave them do whatever they want
        if (gettype($price) !== "string") {
            // for every other type, lets format it appropriately
            $price = number_format($price, 8, '.', '');
        }
        
        if (gettype($opt['stopPrice']) !== "string") {
            // for every other type, lets format it appropriately
            $opt['stopPrice'] = number_format($opt['stopPrice'], 8, '.', '');
        }

        if (!$opt['closePosition']) {
            if (!$quantity || is_numeric($quantity) === false) {
                // WPCS: XSS OK.
                echo "warning: quantity expected numeric got " . gettype($quantity) . PHP_EOL;
            }
        } else {
            if ($quantity > 0) {
                echo "warning: cannot set quantity with closePosition flag" . PHP_EOL;
            }
        }

        if (is_string($price) === false) {
            // WPCS: XSS OK.
            echo "warning: price expected string got " . gettype($price) . PHP_EOL;
        }

        if ($type === "LIMIT" || $type === "STOP" || $type === "TAKE_PROFIT") {
            $opt["price"] = $price;
            $opt["timeInForce"] = "GTC";
        }

        $qstring = ($test == false) ? $this->endpoints['order'] : $this->endpoints['order/test'];
        return $this->httpRequest($qstring, "POST", $opt, true);
    }
    
    public function takeProfit(string $forSide, string $symbol, $triggerPrice, $quantity = null, $limitPrice = null, $test = false) {
        $opt = [ 
            'stopPrice' => $triggerPrice 
        ];
        if (!$quantity) {
            $opt['closePosition'] = true;
        } else {
            $opt['reduceOnly'] = true;
        }
        $type = ($limitPrice ? 'TAKE_PROFIT' : 'TAKE_PROFIT_MARKET');
        return $this->order($forSide, $symbol, $quantity, $limitPrice, $type, $opt, $test);
    }
    
    public function stopLoss(string $side, string $symbol, $triggerPrice, $quantity = null, $limitPrice = null, $test = false) {
        $opt = [ 
            'stopPrice' => $triggerPrice 
        ];
        if (!$quantity) {
            $opt['closePosition'] = true;
        } else {
            $opt['reduceOnly'] = true;
        }
        $type = ($limitPrice ? 'STOP' : 'STOP_MARKET');
        return $this->order($side, $symbol, $quantity, $limitPrice, $type, $opt, $test);
    }
    
    public function stopEntry(string $side, string $symbol, $triggerPrice, $quantity, $limitPrice = null, $test = false) {
        $opt = [ 
            'stopPrice' => $triggerPrice 
        ];
        $type = ($limitPrice ? 'STOP' : 'STOP_MARKET');
        return $this->order($side, $symbol, $quantity, $limitPrice, $type, $opt, $test);
    }
    
    public function limitEntry(string $forSide, string $symbol, $limitPrice, $quantity, $test = false) {
        return $this->order($forSide, $symbol, $quantity, $limitPrice, 'LIMIT', [], $test);
    }
    
    public function marketEntry(string $forSide, string $symbol, $price, $quantity, $test = false) {
        return $this->order($forSide, $symbol, $quantity, $price, 'MARKET', [], $test);
    }
    
    public function stopTrade(string $side, string $symbol, $price, $quantity, $reduceOnly, $test = false) {
        $opt = [ 
            'stopPrice' => $price,
            'reduceOnly' => $reduceOnly
        ];
        return $this->order($side, $symbol, $quantity, null, 'STOP_MARKET', $opt, $test);
    }
    
    public function limitTrade(string $side, string $symbol, $price, $quantity, $reduceOnly, $test = false) {
        $opt = [ 
            'reduceOnly' => $reduceOnly
        ];
        return $this->order($side, $symbol, $quantity, $price, 'LIMIT', $opt, $test);
    }
    
    public function marketTrade(string $side, string $symbol, $quantity, $reduceOnly, $test = false) {
        $opt = [ 
            'reduceOnly' => $reduceOnly
        ];
        return $this->order($side, $symbol, $quantity, null, 'MARKET', $opt, $test);
    }
    
    public function balances($priceData = false)
    {
        if (is_array($priceData) === false) {
            $priceData = false;
        }

        $account = $this->httpRequest($this->endpoints['account'], "GET", [], true);

        if (is_array($account) === false) {
            echo "Error: unable to fetch your account details" . PHP_EOL;
        }

        foreach ($account as $data) {
            $ret['balances'][] = [
                "asset" => $data['asset'], 
                "free" => floatval($data['availableBalance']),
                "locked" => max(0, floatval($data['balance']) - floatval($data['availableBalance']))
            ];            
        }
        
        return $this->balanceData($ret, $priceData);
    }
    
}
