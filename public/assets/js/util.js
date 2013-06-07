function sleep(sleep_time)
{
	var start_time = new Date().getTime();
	var now = new Date().getTime();
	while (now < start_time + sleep_time) {
		now = new Date().getTime();
	}
	return;
}
