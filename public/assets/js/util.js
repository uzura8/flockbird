function sleep(sleep_time)
{
	var start_time = new Date().getTime();
	var now = new Date().getTime();
	while (now < start_time + sleep_time) {
		now = new Date().getTime();
	}
	return;
}

function objectSort(object) {
	var isReverse = (arguments.length > 1) ? arguments[1] : false;

	var sorted = {};
	var array = [];
	for (key in object) {
		if (object.hasOwnProperty(key)) {
			array.push(key);
		}
	}
	if (isReverse) {
		array.reverse();
	} else {
		array.sort();
	}
	for (var i = 0; i < array.length; i++) {
		sorted[array[i]] = object[array[i]];
	}
	return sorted;
}
