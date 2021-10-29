<?php
$sub_menu = "920110";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '출하관리';
// include_once('./_top_menu_oro.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];

$sql_common = " FROM {$g5['order_out_table']} AS oro
                    LEFT JOIN {$g5['order_item_table']} AS ori ON ori.ori_idx = oro.ori_idx
                    LEFT JOIN {$g5['order_table']} AS ord ON ord.ord_idx = oro.ord_idx
                    LEFT JOIN {$g5['bom_table']} AS bom ON bom.bom_idx = ori.bom_idx
                    LEFT JOIN {$g5['order_out_practice_table']} AS oop ON oop.oro_idx = oro.oro_idx
"; 

$where = array();
// 디폴트 검색조건 (used 제외)
$where[] = " ori.ori_status NOT IN('trash','delete','del','cancel') AND oro.oro_status NOT IN ('trash','delete','del','cancel') AND oro.com_idx = '".$_SESSION['ss_com_idx']."' ";

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'ori_idx' || $sfl == 'itm_idx' || $sfl == 'oro.oro_idx' || $sfl == 'oro.com_idx_customer' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'bct_id' ) :
			$where[] = " {$sfl} LIKE '".trim($stx)."%' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

if($ord_date){
    $where[] = " ord.ord_date = '".$ord_date."' ";
}
if($oro_date_plan){
    $where[] = " oro_date_plan = '".$oro_date_plan."' ";
}
if($oro_date){
    $where[] = " oro_date = '".$oro_date."' ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

//if (!$sst) {
    //$sst = "ord.ord_reg_dt, oro.ord_idx, oro.ori_idx, oro.oro_idx";
    $sst = "ord.ord_reg_dt desc, oro.ori_idx desc";
    //$sod = "desc";
    $sod = "";
//}
//$sql_group = " GROUP BY ord.ord_idx ";
$sql_order = " ORDER BY {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 100;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "SELECT oro.*, oop.orp_idx, ori.bom_idx, bom.bom_name, ord.ord_date, ord.ord_reg_dt
        {$sql_common} {$sql_search} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
//print_r3($sql);
$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
?>
<style>
.td_bom_name {text-align:left !important;}
.td_orp_idx{position:relative;}
.td_orp_idx span{position:absolute;right:0;top:-4px;color:orange;font-size:0.8em;}
.td_oro_part_no, .td_com_name, .td_oro_maker
,.td_oro_items, .td_oro_items_title {text-align:left !important;}
.td_oro_count{position:relative;text-align:right !important;width:120px;}
.td_oro_count.td_diff_cnt{background:#737132;}
.td_oro_count .tip_cnt{position:absolute;top:2px;left:2px;font-size:0.8em;background:#da1111;color:#fff;padding:0 5px;border-radius:3px;}
.td_oro_count .tip_cnt:after{display:block;content:'';position:absolute;bottom:4px;right:-9px;width: 0px;height: 0px;border-top:7px solid none; border-bottom:7px solid #da1111;border-right: 7px solid transparent;border-left: 7px solid  transparent; transform:rotate(90deg);}
.span_oro_price {margin-left:20px;}
.span_oro_price b, .span_bit_count b {color:#737132;font-weight:normal;}
#modal01 table ol {padding-right: 20px;text-indent: -12px;padding-left: 12px;}
#modal01 form {overflow:hidden;}
.ui-dialog .ui-dialog-titlebar-close span {
    display: unset;
    margin: -8px 0 0 -8px;
}
.sch_label{position:relative;}
.sch_label span{position:absolute;top:-23px;left:0px;z-index:2;}
.sch_label .data_blank{position:absolute;top:3px;right:-18px;z-index:2;font-size:1.1em;cursor:pointer;}
.slt_label{position:relative;}
.slt_label span{position:absolute;top:-23px;left:0px;z-index:2;}
.slt_label .data_blank{position:absolute;top:3px;right:-18px;z-index:2;font-size:1.1em;cursor:pointer;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
	<label for="sfl" class="sound_only">검색대상</label>
	<select name="sfl" id="sfl">
		<option value="oro.com_idx_customer"<?php echo get_selected($_GET['sfl'], "oro.com_idx_customer"); ?>>거래처번호</option>
		<option value="bom_name"<?php echo get_selected($_GET['sfl'], "bom_name"); ?>>품명</option>
		<option value="oro.ord_idx"<?php echo get_selected($_GET['sfl'], "oro.ord_idx"); ?>>수주번호</option>
		<option value="oro.ori_idx"<?php echo get_selected($_GET['sfl'], "oro.ori_idx"); ?>>수주상품번호</option>
	</select>
	<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
	<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
	<label for="ord_date" class="sch_label">
		<span>수주일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
		<input type="text" name="ord_date" value="<?php echo $ord_date ?>" id="ord_date" readonly class="frm_input readonly" placeholder="수주일" style="width:100px;" autocomplete="off">
	</label>
	<label for="oro_date_plan" class="sch_label">
		<span>출하예정일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
		<input type="text" name="oro_date_plan" value="<?php echo $oro_date_plan ?>" id="oro_date_plan" readonly class="frm_input readonly" placeholder="출하예정일" style="width:100px;" autocomplete="off">
	</label>
	<label for="oro_date" class="sch_label">
		<span>실출하일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
		<input type="text" name="oro_date_plan" value="<?php echo $oro_date ?>" id="oro_date" readonly class="frm_input readonly" placeholder="실출하일" style="width:100px;" autocomplete="off">
	</label>
	<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p><span style="color:red;">[조정필요]빨간색 깜빡임</span>은 수주상품의 총갯수와 전체 납품 수량이 일치하지 않다는 의미 입니다.(갯수를 맞춰 주셔야 합니다.)</p> 
</div>

<div class="select_input">
    <h3>선택목록 데이터일괄 입력</h3>
    <p style="padding:30px 0 20px">
        <label for="" class="slt_label">
            <span>출하예정일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <input type="text" id="o_date_plan" value="" class="tbl_input o_date_plan" style="width:80px;" autocomplete="off">
        </label>
        <label for="" class="slt_label">
            <span>출하일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <input type="text" id="o_date" value="" class="tbl_input o_date_plan" style="width:80px;" autocomplete="off">
        </label>
        <label for="" class="slt_label">
            <span>출하처<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <input type="hidden" id="com_idx_shipto" value="">
            <input type="text" id="com_name2" value="" add="./customer_shipto_select.php?file_name=<?php echo $g5['file_name']?>" class="frm_input readonly" readonly autocomplete="off">
        </label>
        <label for="" class="slt_label">
            <span>상태<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <select name="o_status" id="o_status">
                <option value="">-선택-</option>
                <?=$g5['set_oro_status_value_options']?>
            </select>
        </label>
        <input type="button" id="slt_input" onclick="slet_input(document.getElementById('form01'));" value="선택항목 일괄입력" class="btn btn_02">
    </p>
</div>
<script>
$('.data_blank').on('click',function(e){
    e.preventDefault();
    //$(this).parent().siblings('input').val('');
    var obj = $(this).parent().next();
    if(obj.prop("tagName") == 'INPUT'){
        if(obj.attr('type') == 'hidden'){
            obj.val('');
            obj.siblings('input').val('');
        }else if(obj.attr('type') == 'text'){
            obj.val('');
        }
    }else if(obj.prop("tagName") == 'SELECT'){
        obj.val('');
    }
});
</script>
<form name="form01" id="form01" action="./order_out_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="com_idx" value="<?php echo $_SESSION['ss_com_idx'] ?>">
<input type="hidden" name="token" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" id="oro_list_chk">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">번호</th>
        <th scope="col">납품처</th>
        <th scope="col">제품(수주상품번호)</th>
        <th scope="col">납품수량</th>
        <th scope="col">생산실행</th>
        <th scope="col">출하예정일</th>
        <th scope="col">출하일</th>
        <th scope="col">출하처</th>
        <th scope="col">수주번호</th>
        <th scope="col">상태</th>
        <th scope="col">관리</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        //print_r3($row);
        // 실행계획 건수
        $sql2 = " SELECT COUNT(orp_idx) AS cnt FROM {$g5['order_out_practice_table']} WHERE oro_idx = '".$row['oro_idx']."' ";
        $orp_exist = sql_fetch($sql2,1);
        $row['orp_cnt'] = $orp_exist['cnt'];
        if($row['orp_cnt']) {
            $row['orp_count'] = '<a href="./order_practice_list.php?sfl=oop.orp_idx&stx='.$row['orp_idx'].'">'.$row['orp_cnt'].'건</a>';
        }
        else {
            $row['orp_count'] = '<a href="./order_practice_form.php?w=&oro_idx='.$row['oro_idx'].'" target="_blank">생성하기</a>';
        }
        
        //ori_idx상품의 갯수와 ori_idx해당 전체 oro_idx.들의 합을 계산
        $ori_sql = sql_fetch(" SELECT ori_count FROM {$g5['order_item_table']} WHERE ord_idx = '{$row['ord_idx']}' AND ori_idx = '{$row['ori_idx']}' AND ori_status NOT IN('trash','delete','del','cancel') ");
        $oro_sql = sql_fetch(" SELECT SUM(oro_count) AS total_cnt FROM {$g5['order_out_table']} WHERE ord_idx = '{$row['ord_idx']}' AND ori_idx = '{$row['ori_idx']}' AND oro_status NOT IN('trash','delete','del','cancel') ");
        $cnt_mod = ($ori_sql['ori_count'] != $oro_sql['total_cnt']) ? 'txt_redblink' : '';

        $s_mod = '<a href="./order_out_form.php?'.$qstr.'&amp;w=u&amp;oro_idx='.$row['oro_idx'].'" class="btn btn_03">수정</a>';
        $s_copy = '<a href="./order_out_form.php?'.$qstr.'&w=c&oro_idx='.$row['oro_idx'].'" class="btn btn_03" style="margin-right:5px;">복제</a>';

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['oro_idx'] ?>">
        <td class="td_chk">
            <input type="hidden" name="ord_idx[<?php echo $row['oro_idx'] ?>]" value="<?php echo $row['ord_idx'] ?>" class="ord_idx_<?php echo $row['oro_idx'] ?>">
            <input type="hidden" name="ori_idx[<?php echo $row['oro_idx'] ?>]" value="<?php echo $row['ori_idx'] ?>" class="ori_idx_<?php echo $row['oro_idx'] ?>">
            <input type="hidden" name="oro_idx[<?php echo $row['oro_idx'] ?>]" value="<?php echo $row['oro_idx'] ?>" class="oro_idx_<?php echo $row['oro_idx'] ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['oro_name']); ?> <?php echo get_text($row['oro_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $row['oro_idx'] ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_oro_idx"><?=$row['oro_idx']?></td><!-- 출하idx -->
        <td class="td_com_name"><a href="?sfl=oro.com_idx_customer&stx=<?=$row['com_idx_customer']?>"><?=$g5['customer'][$row['com_idx_customer']]['com_name']?></a></td><!-- 납품처 -->
        <td class="td_bom_name"><?=$row['bom_name']?>(<?=$row['ori_idx']?>)</td><!-- 제품 -->
        <td class="td_oro_count">
            <?php if($cnt_mod){ ?>
            <span class="tip_cnt txt_blueblink">조정필요</span>
            <?php } ?>
            <span><?=$ori_sql['ori_count']?>개중</span>
            <input type="text" name="oro_count[<?php echo $row['oro_idx'] ?>]" value="<?=$row['oro_count']?>" class="tbl_input sit_amt oro_count_<?php echo $row['oro_idx'] ?>" style="width:45px;">
        </td><!-- 납품수량 -->
        <td class="td_orp_idx">
            <input type="hidden" name="orp_cnt[<?php echo $row['oro_idx'] ?>]" value="<?php echo $row['orp_cnt'] ?>" class="orp_cnt_<?php echo $row['oro_idx'] ?>">
            <?=$row['orp_count']?>
            <?=(($row['orp_idx'])?'<span>'.$row['orp_idx'].'</span>':'')?>
        </td><!-- 실행계획 -->
        <td class="td_oro_date_plan td_oro_date_plan_<?=$row['oro_idx']?>">
            <input type="text" name="oro_date_plan[<?php echo $row['oro_idx'] ?>]" value="<?=(($row['oro_date_plan'] == '0000-00-00')?'':$row['oro_date_plan'])?>" readonly class="tbl_input readonly oro_date_plan_<?php echo $row['oro_idx'] ?>" style="width:80px;text-align:center;">
        </td><!-- 출하예정일 -->
        <td class="td_oro_date td_oro_date_<?=$row['oro_idx']?>">
            <input type="text" name="oro_date[<?php echo $row['oro_idx'] ?>]" value="<?=(($row['oro_date'] == '0000-00-00')?'':$row['oro_date'])?>" readonly class="tbl_input readonly oro_date_<?php echo $row['oro_idx'] ?>" style="width:80px;text-align:center;">
        </td><!-- 출하일 -->
        <td class="td_com_shipto td_com_shipto_<?=$row['oro_idx']?>">
            <input type="hidden" name="com_idx_shipto[<?php echo $row['oro_idx'] ?>]" class="com_idx_shipto_<?php echo $row['oro_idx'] ?>" value="<?php echo $row['com_idx_shipto']?>">
            <input type="text" value="<?php echo $g5['customer'][$row['com_idx_shipto']]['com_name']?>" readonly class="tbl_input readonly com_name_sihpto_<?php echo $row['oro_idx'] ?>" style="width:110px;">
            <!--a href="?sfl=oro.com_idx_shipto&stx=<?php //echo $row['com_idx_shipto']?>"><?php //echo $g5['customer'][$row['com_idx_shipto']]['com_name']?></a-->
        </td><!-- 출하처 -->
        <td class="td_ord_idx"><a href="?sfl=oro.ord_idx&stx=<?=$row['ord_idx']?>"><?=$row['ord_idx']?></a></td><!-- 수주번호 -->
        <td class="td_oro_status td_oro_status_<?=$row['oro_idx']?>">
            <input type="hidden" name="oro_status[<?php echo $row['oro_idx'] ?>]" class="oro_status_<?php echo $row['oro_idx'] ?>" value="<?php echo $row['oro_status']?>">
            <input type="text" value="<?php echo $g5['set_oro_status_value'][$row['oro_status']]?>" readonly class="tbl_input readonly oro_status_name_<?php echo $row['oro_idx'] ?>" style="width:60px;text-align:center;">
        </td><!-- 상태 -->
        <td class="td_mng">
			<?=$s_copy?>
			<?=$s_mod?>
		</td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='21' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if (!auth_check($auth[$sub_menu],'w')) { ?>
    <input type="submit" name="act_button" value="실행계획묶음등록" onclick="document.pressed=this.value" class="btn btn_03">
    <input type="submit" name="act_button" value="실행계획개별등록" onclick="document.pressed=this.value" class="btn btn_03">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <!--a href="./order_out_form.php" id="member_add" class="btn btn_01">추가하기</a-->
    <?php } ?>

</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<script>
$("input[name*=_date],input[id*=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

// 출하처찾기 버튼 클릭
$("#com_name2").click(function(e) {
    e.preventDefault();
    var href = $(this).attr('add');
    winCustomerSelect = window.open(href, "winCustomerSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
    winCustomerSelect.focus();
});

// 마우스 hover 설정
$(".tbl_head01 tbody tr").on({
    mouseenter: function () {
        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#0b1938');
        
    },
    mouseleave: function () {
        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
    }    
});

// 가격 입력 쉼표 처리
$(document).on( 'keyup','input[name^=oro_price], input[name^=oro_count], input[name^=oro_lead_time]',function(e) {
    if(!isNaN($(this).val().replace(/,/g,'')))
        $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
});

// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

function slet_input(f){
    var chk_count = 0;
    var chk_idx = [];
    //var dt_pattern = new RegExp("^(\d{4}-\d{2}-\d{2})$");
    var dt_pattern = /^(\d{4}-\d{2}-\d{2})$/;
    for(var i=0; i<f.length; i++){
        if(f.elements[i].name == "chk[]" && f.elements[i].checked){
            chk_idx.push(f.elements[i].value);
            chk_count++;
        }
    }
    if (!chk_count) {
        alert("일괄입력할 출하목록을 하나 이상 선택하세요.");
        return false;
    }



    var o_date_plan = $.trim(document.getElementById('o_date_plan').value);
    var o_date = $.trim(document.getElementById('o_date').value);
    var com_idx_shipto = document.getElementById('com_idx_shipto').value;
    var com_name2 = document.getElementById('com_name2').value;
    var o_status = document.getElementById('o_status').value;
    var o_status_name = $('#o_status').find('option[value="'+o_status+'"]').text();
    //출하예정의 날짜 형식 체크
    if(!dt_pattern.test(o_date_plan) && o_date_plan != ''){
        alert('날짜 형식에 맞는 데이터를 입력해 주세요.\r\n예)2021-02-05');
        document.getElementById('o_date_plan').value = '0000-00-00';
        document.getElementById('o_date_plan').focus();
        return false;
    }
    //출하일 날짜 형식 체크
    if(!dt_pattern.test(o_date) && o_date.length != ''){
        alert('날짜 형식에 맞는 데이터를 입력해 주세요.\r\n예)2021-02-05');
        document.getElementById('o_date').value = '0000-00-00';
        document.getElementById('o_date').focus();
        return false;
    }
    //console.log(chk_idx);return;
    for(var idx in chk_idx){
        //console.log(idx);continue;
        if(o_date_plan){
            $('.td_oro_date_plan_'+chk_idx[idx]).find('input[type="text"]').val(o_date_plan);
        }
        if(o_date){
            $('.td_oro_date_'+chk_idx[idx]).find('input[type="text"]').val(o_date);
        }
        if(com_idx_shipto){
            $('.td_com_shipto_'+chk_idx[idx]).find('input[type="hidden"]').val(com_idx_shipto);
            $('.td_com_shipto_'+chk_idx[idx]).find('input[type="text"]').val(com_name2);
        }
        if(o_status){
            $('.td_oro_status_'+chk_idx[idx]).find('input[type="hidden"]').val(o_status);
            $('.td_oro_status_'+chk_idx[idx]).find('input[type="text"]').val(o_status_name);
        }
    }
}    

function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }
    else if(document.pressed == "실행계획개별등록"){
        //var chk_idx = [];
        for(var i=0; i<f.length; i++){
            if(f.elements[i].name == "chk[]" && f.elements[i].checked){
                //출하갯수 데이터가 없으면 등록 안됨
                if($('.oro_count_'+f.elements[i].value).val() == '0'){
                    alert('납품수량값이 없는 항목은 생산실행에 등록할 수 없습니다.');
                    $('.oro_count_'+f.elements[i].value).focus();
                    return false;
                }
                // 실행계획 레코드가 한 개이상 존재하면 등록 안됨
                if($('.orp_cnt_'+f.elements[i].value).val() != '0'){
                    alert('이미 실행계획이 등록된 항목을 선택하셨네요.\r\n생산실행이 [생성하기]으로 표시된  항목만 선택가능합니다.');
                    //$('.orp_cnt_'+f.elements[i].value).focus();
                    return false;
                }
                // 출하예정일 없으면 등록 안됨
                if($('.oro_date_plan_'+f.elements[i].value).val() == '0000-00-00'){
                    alert('출하예정일이 없는 항목은 생산실행에 등록할 수 없습니다.');
                    $('.oro_date_plan_'+f.elements[i].value).focus();
                    return false;
                }
                // 출하처 데이터가 없으면 등록 안됨
                if($('.com_idx_shipto_'+f.elements[i].value).val() == ''){
                    alert('출하처 데이터가 없는 항목은 생산실행에 등록할 수 없습니다.');
                    $('.com_name_shipto_'+f.elements[i].value).focus();
                    return false;
                }
                // 출하 상태값이 '출하완료','삭제','취소'로 설정된 항목 등록 안됨
                if($('.oro_status_'+f.elements[i].value).val() == 'ok' || $('.oro_status_'+f.elements[i].value).val() == 'trash' || $('.oro_status_'+f.elements[i].value).val() == 'del' || $('.oro_status_'+f.elements[i].value).val() == 'delete' || $('.oro_status_'+f.elements[i].value).val() == 'cancel'){
                    alert('[출하완료/삭제/취소]등의 상태값의 항목은 생산실행에 등록할 수 없습니다.');
                    $('.oro_status_name_'+f.elements[i].value).focus();
                    return false;
                }
            }
        }
        //console.log(chk_idx);
        //return false;


        var practice_tmp_create_url = "./order_practice_tmp_create.php";
        $('#form01').attr({'action':practice_tmp_create_url}).submit();
        return false;
    }
    else if(document.pressed == "실행계획묶음등록"){
        for(var i=0; i<f.length; i++){
            //alert(1);return;
            if(f.elements[i].name == "chk[]" && f.elements[i].checked){
                //출하갯수 데이터가 없으면 등록 안됨
                if($('.oro_count_'+f.elements[i].value).val() == '0'){
                    alert('납품수량값이 없는 항목은 생산실행에 등록할 수 없습니다.');
                    $('.oro_count_'+f.elements[i].value).focus();
                    return false;
                }
                // 실행계획 레코드가 한 개이상 존재하면 등록 안됨
                if($('.orp_cnt_'+f.elements[i].value).val() != '0'){
                    alert('이미 실행계획이 등록된 항목을 선택하셨네요.\r\n생산실행이 [생성하기]으로 표시된  항목만 선택가능합니다.');
                    //$('.orp_cnt_'+f.elements[i].value).focus();
                    return false;
                }
                // 출하예정일 없으면 등록 안됨
                if($('.oro_date_plan_'+f.elements[i].value).val() == '0000-00-00' || $('.oro_date_plan_'+f.elements[i].value).val() == ''){
                    alert('출하예정일이 없는 항목은 생산실행에 등록할 수 없습니다.');
                    $('.oro_date_plan_'+f.elements[i].value).focus();
                    return false;
                }
                // 출하처 데이터가 없으면 등록 안됨
                if($('.com_idx_shipto_'+f.elements[i].value).val() == ''){
                    alert('출하처 데이터가 없는 항목은 생산실행에 등록할 수 없습니다.');
                    $('.com_name_shipto_'+f.elements[i].value).focus();
                    return false;
                }
                // 출하 상태값이 '출하완료','삭제','취소'로 설정된 항목 등록 안됨
                if($('.oro_status_'+f.elements[i].value).val() == 'ok' || $('.oro_status_'+f.elements[i].value).val() == 'trash' || $('.oro_status_'+f.elements[i].value).val() == 'del' || $('.oro_status_'+f.elements[i].value).val() == 'delete' || $('.oro_status_'+f.elements[i].value).val() == 'cancel'){
                    alert('[출하완료/삭제/취소]등의 상태값의 항목은 생산실행에 등록할 수 없습니다.');
                    $('.oro_status_name_'+f.elements[i].value).focus();
                    return false;
                }
            }
        }
        //return false;
        var practice_tmp_bundle_create_url = "./order_practice_tmp_bundle_create.php";
        $('#form01').attr({'action':practice_tmp_bundle_create_url}).submit();
        return false;
    }

    return true;
}


function form02_submit(f) {
    if (!f.file_excel.value) {
        alert('엑셀 파일(.xls)을 입력하세요.');
        return false;
    }
    else if (!f.file_excel.value.match(/\.xls$|\.xlsx$/i) && f.file_excel.value) {
        alert('엑셀 파일만 업로드 가능합니다.');
        return false;
    }

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>