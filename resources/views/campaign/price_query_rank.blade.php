<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>报价挖矿活动排名</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel="stylesheet" type="text/css" href="/css/price_query_rank.css">
</head>
<body>
    <div class="container-fluid header">
        <div class="row header-img">
            <img src="/img/price_query_rank/header.png" class="img-responsive center-block" alt="">
        </div>
        <div class="header-text">
            <p>BVC 报价即挖矿活动</p>
            <p>2019.7.8 - 2019.7.12</p>
            <p>Powered by Proton</p>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row th-text">
            <div class="col-xs-7"><p class="text-center">参与者</p></div>
            <div class="col-xs-2 th-text-3"><p class="text-center">入群数</p></div>
            <div class="col-xs-3 th-text-3"><p class="text-center">报价次数</p></div>
        </div>
    </div>
    <div  class="container" id="content">
        @if($userRank)
            <div class="row rank-list self-bg">
                <div class="col-xs-2 text-center">
                    @if($userRank->rank == 1)
                        <img src="/img/price_query_rank/no-01.png" class="img-responsive center-block img-no" alt="">
                    @elseif($userRank->rank == 2)
                        <img src="/img/price_query_rank/no-02.png" class="img-responsive center-block img-no" alt="">
                    @elseif($userRank->rank == 3)
                        <img src="/img/price_query_rank/no-03.png" class="img-responsive center-block img-no" alt="">
                    @else
                        <p>{{$userRank->rank}}</p>
                    @endif
                </div>
                <div class="col-xs-2 ">
                    <img src="{{$userRank->avatar}}" class="center-block img-avatar" alt="">
                </div>
                <div class="col-xs-3">
                    <p>{{$userRank->nickname}}</p>
                </div>
                <div class="col-xs-2 text-right">
                    <p>{{$userRank->group_count}}</p>
                </div>
                <div class="col-xs-3 text-right query-total">
                    <p>{{$userRank->total}}</p>
                </div>
            </div>
            <div class="divider"> </div>
        @endif
        @foreach ($rankList['data'] as $rank)
            <div class="row rank-list @if($userRank && $userRank->user_id == $rank->user_id) self-bg @endif">
                <div class="col-xs-2 text-center">
                    @if($rank->rank == 1)
                        <img src="/img/price_query_rank/no-01.png" class="img-responsive center-block img-no" alt="">
                    @elseif($rank->rank == 2)
                        <img src="/img/price_query_rank/no-02.png" class="img-responsive center-block img-no" alt="">
                    @elseif($rank->rank == 3)
                        <img src="/img/price_query_rank/no-03.png" class="img-responsive center-block img-no" alt="">
                    @else
                        <p>{{$rank->rank}}</p>
                    @endif
                </div>
                <div class="col-xs-2 ">
                    <img src="{{$rank->avatar}}" class="center-block img-avatar" alt="">
                </div>
                <div class="col-xs-3">
                    <p>{{$rank->nickname}}</p>
                </div>
                <div class="col-xs-2 text-right">
                    <p>{{$rank->group_count}}</p>
                </div>
                <div class="col-xs-3 text-right query-total">
                    <p>{{$rank->total}}</p>
                </div>
            </div>
            <div class="divider"> </div>
        @endforeach

    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
 <script type="text/javascript">


 </script>
</body>
</html>
