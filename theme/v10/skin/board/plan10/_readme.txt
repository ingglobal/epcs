디비 추가 필드
> skin/list.php 맨 하단에 정의되어 있어요.


게시판 필드 재활용
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
ca_name: 종목


추가 필드 활용
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
wr_1 업체번호
wr_2 설비번호
wr_3 정비일자
wr_4 며칠전부터
wr_5 반복주기 (매일, 첫날만, 2일마다, 3일마다, 7일마다, 마지막날만)
wr_6 몇시 (9시, 13시, 18시, 23시)
wr_7 연락처(이름, 직책, 휴대폰, 이메일) - 여러명
wr_9_serial send_type=메시지발송타입(email,sms,push)

^^^^^^^^^^^^^
기타 serialized 항목들

^^^^^^^^^^^^^

wr_8 => 검색키로 사용(:com_name=업체명:,:mms_name=설비명:,...)
wr_9 => serialized (추가 항목들 쭉~~ )
> 검색키들 + 추가필드들(com_name, mms_name, )

wr_10 상태값



// 게시판 설정
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
bo_1 sub_menu 코드

bo_6 발송시간설정
bo_7 serialized 설정값들
bo_8 알림주기 설정
bo_9 상태값 설정


