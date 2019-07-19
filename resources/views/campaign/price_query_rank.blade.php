<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>报价挖矿活动排名</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">

    <link rel="stylesheet" type="text/css" href="/css/price_query_rank.css?v2">
</head>
<body>
    <div class="container-fluid header">
        <div class="header-img">
            <img src="/img/price_query_rank/header.png" class="img-responsive center-block" alt="">
        </div>
        <div class="header-text">
            <p>BCV 报价即挖矿活动</p>
            <p>2019.7.8 - 2019.7.12</p>
            <p>Powered by Proton</p>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row th-text">
            <div class="col-xs-5 col-xs-offset-2"><p class="text-left">参与者</p></div>
            <div class="col-xs-2 th-text-3"><p class="text-center">入群数</p></div>
            <div class="col-xs-3 th-text-3"><p class="text-center">报价次数</p></div>
        </div>
    </div>
    <div  class="container" id="content">
        <div id="scroller">
            <div id="list">
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
                        <div class="col-xs-2">
                            <div class="avatar-text">{{mb_substr($rank->xu_nickname,0, 1)}}</div>
                        </div>
                        <div class="col-xs-4 nickname">
                            <div class="nickname-text">{{$rank->xu_nickname}}</div>
                        </div>
                        <div class="col-xs-1 text-right">
                            <p class="group-count">{{$rank->group_count}}</p>
                        </div>
                        <div class="col-xs-3 text-right query-total">
                            <p>{{$rank->total}}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="pull-loading">
                上拉加载
            </div>
        </div>
    </div>
        <!-- 模态框（Modal） -->
    <div class="main-modal" id="main-modal" style="@if(!$userJoin) display: block;@else display: none; @endif">
        <div class="main-content">
            <div class="main-header">
                <img src="/img/price_query_rank/bv-logo.png" class="center-block" alt="">
                <p>本次报价即挖矿活动活动由 BCV 主办</p>
            </div>
            <div class="main-body">
                <p>满足活动条件则可瓜分奖池，活动<br>
                    最终解释权归 BCV 所有<br><br>
                    活动时间<br>
                    2019.7.8 - 2019.7.12
                </p>
            </div>
            <div class="main-footer">
                <img src="/img/price_query_rank/proton@2x.png"  class="center-block" alt="">
                <button type="button" id="joinButton" data-loading-text="加入中..." class="center-block btn btn-join" autocomplete="off">
                    立即参加
                </button>
                <button type="button" id="cancelButton" class="center-block btn-cancel" autocomplete="off">
                    暂不参加
                </button>
            </div>
        </div>
    </div>
    <div class="alert"></div>
    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
    <script src="/js/iscroll.js"></script>
   <!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript">

        $(function(){

            $('#joinButton').on('click', function () {
                $(this).attr("disabled","disabled");
                $.ajax({
                    url: '/api/v1/price/query/'+ {{$user->id}} + '/join/2' ,
                })
                .done(function(data) {
                    if (data.code == 0) {
                        $('#main-modal').css("display","none");
                        $('.alert').html(data.message).addClass('alert-info ').show().delay(1500).fadeOut();
                    }

                    if (data.code == 1) {
                        $('.alert').html(data.message).addClass('alert-info ').show().delay(1500).fadeOut();
                    }
                })
                .fail(function() {
                    $('.alert').html('操作失败').addClass('alert-info ').show().delay(1500).fadeOut();
                })
                .always(function() {
                    $('#joinButton').removeAttr("disabled");
                });

            })

            $('#cancelButton').on('click', function () {
                $('#main-modal').css("display","none");
            })

            var myscroll = new iScroll("content", {
                    onScrollMove: function () { //拉动时
                        //上拉加载
                        if (this.y < this.maxScrollY) {
                            $(".pull-loading").html("释放加载");
                            $(".pull-loading").addClass("loading");
                        } else {
                            $(".pull-loading").html("上拉加载");
                            $(".pull-loading").removeClass("loading");
                        }
                    },
                    onScrollEnd: function () { //拉动结束时
                        //上拉加载
                        if ($(".pull-loading").hasClass('loading')) {
                            $(".pull-loading").html("加载中...");
                            pullOnLoad();
                        }
                    }
                });

              //上拉加载函数,ajax
            var page = 2;
            function pullOnLoad() {
                setTimeout(function () {
                    $.ajax({
                        url: "/api/v1/price/query/rank/" + page,
                        success: function (data) {
                            var html = '';
                            if (data.data.length){
                                var html = getHtml(data.data);
                                $("#list").append(html);
                                $('.pull-loading').html("上拉加载");
                            }else{
                                $('.pull-loading').html("没有了哟");
                            }
                            myscroll.refresh();
                            page ++;
                        },
                        error: function () {
                            console.log("出错了");
                        }
                    });
                    myscroll.refresh();
                }, 500);
            }

            function getHtml(data) {
                var html = '';
                $.each(data, function(index, el) {
                        html += '<div class="row rank-list"><div class="col-xs-2 text-center">';
                        if(el.rank == 1) {
                            html += '<img src="/img/price_query_rank/no-01.png" class="img-responsive center-block img-no" alt="">';
                        }
                        else if(el.rank == 2) {
                            html += '<img src="/img/price_query_rank/no-02.png" class="img-responsive center-block img-no" alt="">';
                        }
                        else if(el.rank == 3) {
                           html += '<img src="/img/price_query_rank/no-03.png" class="img-responsive center-block img-no" alt="">' ;
                        }
                        else{
                            html += '<p>' + el.rank + '</p>';
                        }
                        var subHtml = '</div>' +
                            '<div class="col-xs-2">' +
                                '<div class="avatar-text">' + el.xu_nickname.substr(0, 1) + '</div>' +
                        '</div>' +
                        '<div class="col-xs-4 nickname">' +
                            '<div class="nickname-text">' + el.xu_nickname + '</div>' +
                        '</div>' +
                        '<div class="col-xs-1 text-right">' +
                            '<p class="group-count">' + el.group_count + '</p>' +
                        '</div>'+
                        '<div class="col-xs-3 text-right query-total">' +
                            '<p>' + el.total + '</p>' +
                        '</div>' +
                    '</div>';

                    html += subHtml;
                });

                return html;
            }

        });


 </script>
</body>
</html>
