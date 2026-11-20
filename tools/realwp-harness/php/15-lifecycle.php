<?php
require_once __DIR__ . '/00-bootstrap.php';
if( false ){ wp_die(); }
global $wpdb; $B=$wpdb->prefix.'drplus_booking';
$R='DrPlus_Child_Capacity_Repository';
echo "=== END-TO-END: the whole customer journey ===\n\n";
$D='2029-10-01'; $wpdb->query("DELETE FROM {$B} WHERE `date`='{$D}'");
update_option('drplus_capacity_conflict_mode','room');
$ok=0;$fail=0;
function chk($n,$g,$w){global $ok,$fail;$good=($g===$w);$good?$ok++:$fail++;printf("%s %-52s got=%-12s want=%s\n",$good?'PASS':'FAIL',$n,var_export($g,true),var_export($w,true));}

// 1. an empty day: the grid offers the hour and the gate accepts it
$g=$R::count_day_slots('614',$D);
chk('1. empty day: grid has no bookings', count($g), 0);
chk('   gate allows', $R::count_distinct_specialists('614',$D,'10:00:00') < 2, true);

// 2. customer 1 checks out but the payment page is abandoned
$o1=wc_create_order(); $o1->set_status('pending'); $o1->save();
$wpdb->insert($B,array('customer_id'=>0,'specialist_id'=>13,'office_id'=>'614','date'=>$D,
  'start_time'=>'10:00:00','end_time'=>'10:30:00','total_price'=>500000,'order_id'=>$o1->get_id(),
  'order_status'=>'pending','deleted'=>0,'created_at'=>current_time('mysql')));
chk('2. fresh abandoned hold still blocks', $R::count_distinct_specialists('614',$D,'10:00:00'), 1);
chk('   grid agrees', isset($R::count_day_slots('614',$D)['10:00:00'])?$R::count_day_slots('614',$D)['10:00:00']:0, 1);

// 3. customer 2 pays
$o2=wc_create_order(); $o2->set_status('processing'); $o2->save();
$wpdb->insert($B,array('customer_id'=>0,'specialist_id'=>14,'office_id'=>'614','date'=>$D,
  'start_time'=>'10:00:00','end_time'=>'10:30:00','total_price'=>500000,'order_id'=>$o2->get_id(),
  'order_status'=>'processing','deleted'=>0,'created_at'=>current_time('mysql')));
chk('3. after customer 2 pays, the hour is FULL', $R::count_distinct_specialists('614',$D,'10:00:00'), 2);
chk('   grid shows it full', isset($R::count_day_slots('614',$D)['10:00:00'])?$R::count_day_slots('614',$D)['10:00:00']:0, 2);

// 4. customer 3 tries -> must be refused
chk('4. a third booking is refused', $R::count_distinct_specialists('614',$D,'10:00:00') >= 2, true);

// 5. customer 2 cancels
$o2=wc_get_order($o2->get_id()); $o2->set_status('cancelled'); $o2->save();
chk('5. after a cancellation there is room again', $R::count_distinct_specialists('614',$D,'10:00:00'), 1);

// 6. time passes: customer 1's hold expires
$wpdb->update($B,array('created_at'=>gmdate('Y-m-d H:i:s',time()-4*3600)),
  array('order_id'=>$o1->get_id()));
$R::count_day_slots('614',$D);
chk('6. the expired hold is released', $R::count_distinct_specialists('614',$D,'10:00:00'), 0);
chk('   and customer 1s order is cancelled', wc_get_order($o1->get_id())->get_status(), 'cancelled');
chk('   the parent list shows only the cancelled pair', (int)$wpdb->get_var($wpdb->prepare(
  "SELECT COUNT(*) FROM {$B} WHERE `date`=%s AND LOWER(TRIM(order_status)) NOT IN ('cancelled','refunded','failed','on-hold')",$D)), 0);

// 7. the hour is bookable again
chk('7. the freed hour is bookable', $R::count_distinct_specialists('614',$D,'10:00:00') < 2, true);

echo "\n==> LIFECYCLE RESULT: $ok passed, $fail failed\n";
