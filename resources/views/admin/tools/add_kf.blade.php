<a id="add_kf" class="btn btn-sm btn-success"><i class="fa fa-save"> 添加客服</i></a>

<div id="new_kf" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">添加客服</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="inputNewKFA" class="col-sm-2 control-label">客服账号</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input type="text" class="form-control" id="inputNewKFA" placeholder="客服账号">
                                <span class="input-group-addon">@ {{env('WECHAT_OFFICIAL_ACCOUNT_ID')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputNewKFNickName" class="col-sm-2 control-label">客服昵称</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="inputNewKFNickName" placeholder="客服昵称">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputNewKFN" class="col-sm-2 control-label">微信号</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="inputNewKFN" placeholder="微信号">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button id="save_kf" type="button" class="btn btn-primary">提交</button>
            </div>
        </div>
    </div>
</div>