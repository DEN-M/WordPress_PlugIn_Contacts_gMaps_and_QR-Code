$('#qrCode_event_start').datetimepicker({
lang:'de',
i18n:{
de:{
months:[
			'Januar','Februar','März','April',
			'Mai','Juni','Juli','August',
			'September','Oktober','November','Dezember',
			],
dayOfWeek:[
			"So.", "Mo", "Di", "Mi", 
			"Do", "Fr", "Sa.",
			]
		}
	},
timepicker:true,
step:15,
format:'Ymd\\THi' + '00',
minDate:0,
dayOfWeekStart:1,
inline:true
});

$('#qrCode_event_end').datetimepicker({
lang:'de',
i18n:{
de:{
months:[
			'Januar','Februar','März','April',
			'Mai','Juni','Juli','August',
			'September','Oktober','November','Dezember',
			],
dayOfWeek:[
			"So.", "Mo", "Di", "Mi", 
			"Do", "Fr", "Sa.",
			]
		}
	},
timepicker:true,
step:15,
format:'Ymd\\THi' + '00',
minDate:0,
dayOfWeekStart:1,
inline:true
});