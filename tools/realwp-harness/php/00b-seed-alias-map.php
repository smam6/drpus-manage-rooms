<?php
/**
 * 00b-seed-alias-map.php — install the clinic alias map DERIVED FROM THE DUMP.
 *
 * The production site never filled this option in, which is precisely what the
 * shipped version-check.php flags as a FAIL. The three custom-office UUIDs in the
 * booking data all carry office_name = "کلینیک آغازگر", i.e. hospital post 614.
 */
require __DIR__ . '/00-bootstrap.php';
// Real alias map derived from the site's own booking data:
// three custom-office UUIDs all resolve to "کلینیک آغازگر" = hospital post 614.
$map = array(
	'1b9d72ba-f2b2-4dfd-8a3c-5b0f13c5c650' => '614',
	'bfafd267-63c7-4a5b-95d8-02e2690cd7aa' => '614',
	'1ed7bf95-a064-4dc5-a41a-43e1b4494984' => '614',
);
update_option('drplus_capacity_clinic_aliases', $map);
echo "alias map saved: ".count($map)." entries\n";
echo "resolve(614) = ".json_encode(DrPlus_Child_Capacity_Repository::resolve_clinic_identifiers('614'))."\n";
echo "resolve(uuid1) = ".json_encode(DrPlus_Child_Capacity_Repository::resolve_clinic_identifiers('1b9d72ba-f2b2-4dfd-8a3c-5b0f13c5c650'))."\n";
echo "capacity_for(614) = ".DrPlus_Child_Capacity_Service::capacity_for('614')."\n";
echo "capacity_for(uuid1) = ".DrPlus_Child_Capacity_Service::capacity_for('1b9d72ba-f2b2-4dfd-8a3c-5b0f13c5c650')."\n";
