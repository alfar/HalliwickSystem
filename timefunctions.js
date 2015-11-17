function fnGetTime(str)
{
	if (str == null)
	{
		return null;
	}
	
	aryMatch = str.match(/^(0*([0-9]+)[:m])?0*([0-9]*)([,.s]0*([0-9]+))?$/);

	if (aryMatch)
	{
		var mins = 0;
		var secs = 0;
		var hundreds = 0;

		if (aryMatch[2])
		{
			mins = parseInt(aryMatch[2]);

			if (isNaN(mins)) mins = 0;
		}

		if (aryMatch[3])
		{
			secs = parseInt(aryMatch[3]);

			if (isNaN(secs)) secs = 0;
		}

		if (aryMatch[5])
		{
			hundreds = parseFloat(aryMatch[4].replace(/[s,]/, '.'));

			if (isNaN(hundreds)) hundreds = 0;
		}

		if (mins + secs + hundreds > 0)
		{
			return (mins * 60 + secs + hundreds).toFixed(2);
		}
	}
	
	return null;
}

function test_fnGetTime()
{
	alert('01:22.56 => ' + fnGetTime('01:22.56'));
	alert('09.56 => ' + fnGetTime('09.56'));
	alert('26.06 => ' + fnGetTime('26.06'));
	alert('26.09 => ' + fnGetTime('26.09'));
	alert('1:16.56 => ' + fnGetTime('1:16.56'));
	alert('2m08.01 => ' + fnGetTime('2m08.01'));
	alert('02m09.09 => ' + fnGetTime('02m09.09'));
	alert('130.99 => ' + fnGetTime('130.99'));
}
