<?php
return array(
	//'^api/auth(.*)$' => 'auth/api$1',
	'^api/members/?$' => 'member/api/list',
	'^api/member/setting/config(.*)$' => 'member/setting/api/config$1',
	'^api/member/relation/(\d+)/(\w+)$' => 'member/relation/api/update/$1/$2',
	'^api/filetmp/upload(.*)$' => 'filetmp/api/upload$1',
);
