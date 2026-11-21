/*
 * Forum Intelligence Dashboard
 * Copyright (c) 2026 rumpa
 *
 * Small progressive enhancements only; every feature works without JS.
 */
(function () {
	'use strict';

	// Require confirmation before acknowledging an alert.
	function confirmAck(event) {
		var link = event.target.closest('a.fid-ack');
		if (link && link.getAttribute('data-confirm') && !window.confirm(link.getAttribute('data-confirm')))
			event.preventDefault();
	}

	// Larger time series are hard to read; hide rows beyond a sane number of
	// daily stats and offer a toggle.
	function initDailyToggle() {
		var tables = document.querySelectorAll('.fid-table');
		Array.prototype.forEach.call(tables, function (table) {
			var rows = table.querySelectorAll('tbody tr');
			if (rows.length <= 20)
				return;

			var toggle = document.createElement('button');
			toggle.type = 'button';
			toggle.className = 'button fid-collapse-toggle';
			toggle.textContent = '—';

			var collapsed = false;
			toggle.addEventListener('click', function () {
				collapsed = !collapsed;
				Array.prototype.forEach.call(rows, function (row, index) {
					if (index < 10)
						return;
					row.style.display = collapsed ? 'none' : '';
				});
				toggle.textContent = collapsed ? '+' : '—';
			});
			toggle.click();

			table.parentNode.insertBefore(toggle, table);
		});
	}

	document.addEventListener('click', confirmAck);
	if (document.readyState === 'loading')
		document.addEventListener('DOMContentLoaded', initDailyToggle);
	else
		initDailyToggle();
}());
