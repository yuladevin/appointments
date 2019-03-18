/*
 * jQuery DP Pro Appointments
 *
 * Copyright 2015, WPSleek
 *
 * @Web: http://www.wpsleek.com
 * @Email: info@wpsleek.com
 *
 * Depends:
 * jquery.js
 */

(function ($) {
	function DPProAppointments(element, options) {
		this.calendar = $(element);
		console.log('asdasd');
		/* Setting vars*/
		this.settings = $.extend({}, $.fn.dpProAppointments.defaults, options);

		// Touch support
		if ("ontouchstart" in window) {

			this.hasTouch = true;
			this.downEvent = "touchstart.rs";
			this.moveEvent = "touchmove.rs";
			this.upEvent = "touchend.rs";
			this.cancelEvent = 'touchcancel.rs';
		}

		this.init();
	}

	DPProAppointments.prototype = {
		init: function () {
			var instance = this;

			instance._makeResponsive();

			$('.dp_appointments_nav select[multiple!="multiple"]').selectric();

			$('.prev_week', instance.calendar).click(function (e) {
				instance._prevWeek(instance);
			});
			$('.next_week', instance.calendar).click(function (e) {
				instance._nextWeek(instance);
			});

			$('.dp_appointments_nav select', instance.calendar).on('change', function () {

				instance._changeService($(this).val());
				return false;
			});

			//New function for checklist deleding
			$('#appoint_checklist_delete').click(function (event) {
				if (!$('.dp_appointments_content input:checked').length) {
					return;
				}
				var answer = confirm('בטוח למחוק את האירוע?');
				if (!answer) {
					return;
				}

				var promises = [];

				$('.dp_appointments_content input:checked').each(function () {
					var recurring_id = $(this).parent().attr('data-id-recurring-appointment');
					if (recurring_id) {
						$.post(ProAppointmentsAjax.ajaxurl, {
							recurring_id: recurring_id,
							action: 'remove_recurring_appointment',
						});
					} else {
						reqUrl = '../wp-admin/admin.php?page=dpProAppointments-admin&delete_appointment=' + $(this).parent().attr('data-id-appointment') + '&noheader=true';
						$.ajax({
							type: "POST",
							url: reqUrl,
							success: function (data) {
								if (data.redirect) {
									window.location.href = data.redirect;
								}
							}
						});
					}
				});
				instance._changeWeek();
			});


			//New function for checklist sending
			$('#appoint_checklist_push').click(function (event) {
				jQuery('.hide-on-adding-event').slideUp();
				if ((event.which === 1 || event.which === 0)) {
					var dates = [];
					$('.dp_appointments_content input:checked').each(function () {
						dates.push(this.name);
					});

					if (!dates.length) {
						return;
					}

					var jsonString = JSON.stringify(dates);
					//console.log(dates);

					$.ajax({
						type: "POST",
						data: {
							data: jsonString,
							service: instance.settings.service,
							postEventsNonce: ProAppointmentsAjax.postEventsNonce
						},
						url: ProAppointmentsAjax.ajaxurl + '&action=getAppointmentForm&service',
						success: function (data) {

							console.log(data);
							$('.dp_appointments_content', instance.calendar).removeClass('dp_appointments_content_loading').empty().html(data);

							$('.dp_appointments_date', instance.calendar).hide().fadeIn(0);

							$('input[type="checkbox"]', instance.calendar).iCheck({
								checkboxClass: 'icheckbox_flat',
								radioClass: 'iradio_flat',
								increaseArea: '20%' // optional
							});
						}
					});

				}
				// now names contains all of the names of checked checkboxes
				// do something with it
			});

			$(instance.calendar).on('mouseup', '.dp_appointments_date:not(.dp_appointments_closed, .dp_appointments_appointed)', function (event) {

				if ((event.which === 1 || event.which === 0)) {


					//instance._removeElements();

					/*$.post(ProAppointmentsAjax.ajaxurl, { 
						date: $(this).data('appointment-date'), 
						service: instance.settings.service, 
						action: 'getAppointmentForm', 
						postEventsNonce : ProAppointmentsAjax.postEventsNonce 
					},
						function(data) {
	
							$('.dp_appointments_content', instance.calendar).removeClass( 'dp_appointments_content_loading' ).empty().html(data);
							
							$('.dp_appointments_date', instance.calendar).hide().fadeIn(0);
							
							$('input[type="checkbox"]', instance.calendar).iCheck({
								checkboxClass: 'icheckbox_flat',
								radioClass: 'iradio_flat',
								increaseArea: '20%' // optional
							});
							
						}
					);	*/
				}


			});

			$(instance.calendar).on('mouseup', '#delete_appointment', function (event) {
				var answer = confirm('בטוח למחוק את האירוע?');
				if (answer == true) {
					//console.log($(this).attr('data-id-appointment'));
					if ($(this).attr('data-is-home') == 'home') {
						reqUrl = 'wp-admin/admin.php?page=dpProAppointments-admin&delete_appointment=' + $(this).attr('data-id-appointment') + '&noheader=true';
					} else {
						reqUrl = '../wp-admin/admin.php?page=dpProAppointments-admin&delete_appointment=' + $(this).attr('data-id-appointment') + '&noheader=true';
					}
					$.ajax({
						type: "POST",
						url: reqUrl,
						success: function (data, textStatus) {
							console.log('reqUrl' + reqUrl);
							console.log(data.redirect);
							if (data.redirect) {

								// data.redirect contains the string URL to redirect to
								window.location.href = data.redirect;
							}
							instance._changeWeek();



						}
					});

				}

			});
			$(instance.calendar).on('mouseup', '#dp_appointments_send_appointment', function (event) {
				var cout_appointments = $('#cout_appointments', instance.calendar).val();
				if (cout_appointments > 0) {
					if ((event.which === 1 || event.which === 0)) {
						var promises = [];
						while (cout_appointments-- > 0) {
							var date = $('#' + (cout_appointments + 1) + ' #dp_appointments_appointment_date', instance.calendar).val();
							// if ($('#recurrence-type').val() === 'weekly') { // single || weekly
							// 	var name = $('#dp_appointments_appointment_name', instance.calendar).val();
							// 	var postPromise = $.post(ProAppointmentsAjax.ajaxurl, {
							// 		date: date,
							// 		name: name,
							// 		action: 'add_recurring_appointment',
							// 	});
							// 	promises.push(postPromise);
							// } else {
							var name = $('#dp_appointments_appointment_name', instance.calendar).val();
							var email = $('#dp_appointments_appointment_email', instance.calendar).val();
							var phone = $('#dp_appointments_appointment_phone', instance.calendar).val();
							var address = $('#dp_appointments_appointment_address', instance.calendar).val();
							var city = $('#dp_appointments_appointment_city', instance.calendar).val();
							var note = $('#dp_appointments_appointment_note', instance.calendar).val();
							var userid = $('#dp_appointments_appointment_userid', instance.calendar).val();
							var service = $('#dp_appointments_appointment_service', instance.calendar).val();
							var return_url = window.location.href;
							$('.appointments-errors', instance.calendar).hide();

							if ($('#dp_appointments_appointment_name', instance.calendar).val() == '') {
								$('.appointments-errors', instance.calendar).show();

								return false;
							}

							if ($('#dp_appointments_appointment_email', instance.calendar).val() == '') {
								$('.appointments-errors', instance.calendar).show();

								return false;
							}

							if ($('#appointments_event_page_book_terms_conditions', instance.calendar).length) {

								if ($('#appointments_event_page_book_terms_conditions', instance.calendar).is(":checked") == false) {

									$('#appointments_event_page_book_terms_conditions', instance.calendar).focus();

									return false;
								}

							}

							instance._removeElements();

							var $btn_booking = $(this);
							$btn_booking.prop('disabled', true);
							$btn_booking.css('opacity', .6);


							$.post(ProAppointmentsAjax.ajaxurl, {
									date: date,
									name: name,
									email: email,
									phone: phone,
									address: address,
									city: city,
									note: note,
									service: service,
									userid: userid,
									return_url: return_url,
									action: 'sendAppointmentForm',
									postEventsNonce: ProAppointmentsAjax.postEventsNonce
								},
								function (data) {

									instance._changeWeek();

								}
							);
							// }
						}
						if (promises.length) {
							$.when(...promises).done(function (data) {
								instance._changeWeek();
							});
						}
					}
				} else {
					if ((event.which === 1 || event.which === 0)) {

						var date = $('#dp_appointments_appointment_date', instance.calendar).val();
						var name = $('#dp_appointments_appointment_name', instance.calendar).val();
						var email = $('#dp_appointments_appointment_email', instance.calendar).val();
						var phone = $('#dp_appointments_appointment_phone', instance.calendar).val();
						var address = $('#dp_appointments_appointment_address', instance.calendar).val();
						var city = $('#dp_appointments_appointment_city', instance.calendar).val();
						var note = $('#dp_appointments_appointment_note', instance.calendar).val();
						var userid = $('#dp_appointments_appointment_userid', instance.calendar).val();
						var service = $('#dp_appointments_appointment_service', instance.calendar).val();
						var return_url = window.location.href;

						$('.appointments-errors', instance.calendar).hide();

						if ($('#dp_appointments_appointment_name', instance.calendar).val() == '') {
							$('.appointments-errors', instance.calendar).show();

							return false;
						}

						if ($('#dp_appointments_appointment_email', instance.calendar).val() == '') {
							$('.appointments-errors', instance.calendar).show();

							return false;
						}

						if ($('#appointments_event_page_book_terms_conditions', instance.calendar).length) {

							if ($('#appointments_event_page_book_terms_conditions', instance.calendar).is(":checked") == false) {

								$('#appointments_event_page_book_terms_conditions', instance.calendar).focus();

								return false;
							}

						}

						instance._removeElements();

						var $btn_booking = $(this);
						$btn_booking.prop('disabled', true);
						$btn_booking.css('opacity', .6);


						$.post(ProAppointmentsAjax.ajaxurl, {
								date: date,
								name: name,
								email: email,
								phone: phone,
								address: address,
								city: city,
								note: note,
								service: service,
								userid: userid,
								return_url: return_url,
								action: 'sendAppointmentForm',
								postEventsNonce: ProAppointmentsAjax.postEventsNonce
							},
							function (data) {

								instance._changeWeek();

							}
						);
					}
				}

			});

			$(instance.calendar).on('mouseup', '#dp_appointments_cancel_appointment', function (event) {

				if ((event.which === 1 || event.which === 0)) {

					instance._removeElements();

					instance._changeWeek();

				}

			});

			$(instance.calendar).on('mouseup', '.dp_appointments_load_more', function (event) {

				if ((event.which === 1 || event.which === 0)) {

					var $btn = $(this);
					$btn.prop('disabled', true);
					$btn.css('opacity', .6);

					$.post(ProAppointmentsAjax.ajaxurl, {
							limit: $(this).data('limit'),
							page: instance.settings.page,
							show_status: instance.settings.show_status,
							action: 'getMoreAppointments',
							postEventsNonce: ProAppointmentsAjax.postEventsNonce
						},
						function (data) {

							instance.settings.page++;

							$btn.prop('disabled', false);
							$btn.css('opacity', 1);

							$('.dp_appointments_my_appointments_list_wrap', instance.calendar).append(data);
							if ($btn.data('total') <= $('.dp_appointments_my_appointments_list', instance.calendar).length) {

								$btn.hide();

							}

						}
					);

				}

			});

		},

		_makeResponsive: function () {
			var instance = this;

			if (instance.calendar.width() < 500) {

				$(instance.calendar).addClass('dp_appointments_400');

			} else {

				$(instance.calendar).removeClass('dp_appointments_400');

			}
		},

		_prevWeek: function (instance) {
			if (!$('.dp_appointments_content', instance.calendar).hasClass('dp_appointments_content_loading')) {
				instance.settings.actualDay -= 7;
				//instance.settings.actualDay = instance.settings.actualDay == 0 ? 12 : (instance.settings.actualDay);

				var today = new Date();

				if (instance.settings.actualDay <= today.getDate()) {
					$('.prev_week', instance.calendar).css('visibility', 'hidden');
				}

				instance._changeWeek();
			}
		},

		_nextWeek: function (instance) {
			if (!$('.dp_appointments_content', instance.calendar).hasClass('dp_appointments_content_loading')) {
				instance.settings.actualDay += 7;
				//instance.settings.actualDay = instance.settings.actualDay == 13 ? 1 : (instance.settings.actualDay);
				$('.prev_week', instance.calendar).css('visibility', 'visible');

				instance._changeWeek();
			}
		},

		_changeService: function (service) {
			var instance = this;
			if (!$('.dp_appointments_content', instance.calendar).hasClass('dp_appointments_content_loading')) {
				instance.settings.service = service;
				//instance.settings.actualDay = instance.settings.actualDay == 13 ? 1 : (instance.settings.actualDay);

				instance._changeWeek();
			}
		},

		_changeWeek: function () {
			var instance = this;

			instance._removeElements();

			var date_timestamp = Date.UTC(instance.settings.actualYear, (instance.settings.actualMonth - 1), (instance.settings.actualDay)) / 1000;

			$.post(ProAppointmentsAjax.ajaxurl, {
					date: date_timestamp,
					is_admin: instance.settings.isAdmin,
					service: instance.settings.service,
					tooltip: instance.settings.tooltip,
					event_id: instance.settings.event_id,
					author: instance.settings.author,
					is_admin: instance.settings.isAdmin,
					action: 'getWeeklyAppointment',
					postEventsNonce: ProAppointmentsAjax.postEventsNonce
				},
				function (data) {
					var newDate = data.substr(0, data.indexOf(">!]-->")).replace("<!--", "");
					$('span.actual_week', instance.calendar).html(newDate);

					$('.dp_appointments_content', instance.calendar).removeClass('dp_appointments_content_loading').empty().html(data);

					instance.eventDates = $('.dp_appointments_date', instance.calendar);

					$('.dp_appointments_date', instance.calendar).hide().fadeIn(0);
					instance._makeResponsive();
					jQuery('.hide-on-adding-event').slideDown();
				}
			);


		},

		_removeElements: function () {

			var instance = this;

			$('.dp_appointments_date,.dp_appointments_dayname,.dp_appointments_form_new_appointment', instance.calendar).fadeOut(0);
			$('.dp_appointments_content', instance.calendar).addClass('dp_appointments_content_loading');

		}

	}

	$.fn.dpProAppointments = function (options) {

		var dpProAppointments;
		this.each(function () {

			dpProAppointments = new DPProAppointments($(this), options);

			$(this).data("dpProAppointments", dpProAppointments);

		});

		return this;

	}

	/* Default Parameters and Events */
	$.fn.dpProAppointments.defaults = {
		actualMonth: '',
		actualYear: '',
		actualDay: '',
		service: '',
		isAdmin: false,
		defaultDate: '',
		page: 1,
		show_status: true
	};

	$.fn.dpProAppointments.settings = {}

})(jQuery);