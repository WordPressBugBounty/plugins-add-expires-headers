jQuery(document).ready(function ($) {
	resumesse();
	let aehSource = null;
	function setProcessingState(activeBtn = 'deafault') {
		const startBtn = document.getElementById('start-optimization');
		const reverseBtn = document.getElementById('start-reverse');
		const cancelBtn = document.getElementById('cancel-operation');
		const liveProcess = document.getElementById('operation-status');
		const aniStart = document.getElementById('ani-start');
		const logOutput = document.getElementById('log-output');
		startBtn.disabled = true;
		reverseBtn.disabled = true;
		cancelBtn.disabled = true;
		aniStart.style.display = 'none';
		if (activeBtn === 'deafault') {
			startBtn.disabled = false;
			reverseBtn.disabled = false;
		} else if (activeBtn === 'start') {
			startBtn.disabled = true;
			reverseBtn.disabled = true;
			cancelBtn.disabled = false;
			reverseBtn.style.display = 'none';
			cancelBtn.style.display = 'inline-block';
			liveProcess.classList.remove('aeh-not-active');
			logOutput.classList.remove('aeh-not-active');
			aniStart.style.display = 'block';
		} else if (activeBtn === 'reverse') {
			startBtn.style.display = 'none';
			startBtn.disabled = true;
			cancelBtn.disabled = false;
			reverseBtn.disabled = true;
			cancelBtn.style.display = 'inline-block';
			liveProcess.classList.remove('aeh-not-active');
			logOutput.classList.remove('aeh-not-active');
			aniStart.style.display = 'block';
		} else if (activeBtn === 'completed') {
			startBtn.disabled = false;
			reverseBtn.disabled = false;
			cancelBtn.disabled = true;
			startBtn.style.display = 'inline-block';
			reverseBtn.style.display = 'inline-block';
			cancelBtn.style.display = 'none';
			aniStart.style.display = 'none';
		} else if (activeBtn === 'cancelled') {
			startBtn.disabled = false;
			reverseBtn.disabled = false;
			cancelBtn.disabled = true;
			startBtn.style.display = 'inline-block';
			reverseBtn.style.display = 'inline-block';
			cancelBtn.style.display = 'none';
			aniStart.style.display = 'none';
		} else if (activeBtn == 'loading') {
			startBtn.disabled = true;
			reverseBtn.disabled = true;
			cancelBtn.disabled = true;
			aniStart.style.display = 'block';
		}

	}
	function resumesse() {
		setProcessingState('loading');
		fetch(ajaxurl + '?action=check_optimization_status')
			.then(response => response.json())
			.then(data => {
				if (data.running) {
					if (data.direction) {
						setProcessingState('start');
						startSSE('optimization');
					} else {
						setProcessingState('reverse');
						startSSE('reverse');
					}
				} else {
					setProcessingState();
				}
			});
	}
	function startSSE(direction = 'optimization') {
		if (!!window.EventSource) {
			if (aehSource) {
				aehSource.close(); // close old stream if any
			}
			window.currentDirection = direction;
			aehSource = new EventSource(ajaxurl + '?aeh_sse=progress&t=' + new Date().getTime());
			aehSource.onmessage = function (event) {
				const data = JSON.parse(event.data);
				console.log(data);
				$('#optimized_images_number').text(data.optimized_images_number || 0);
				$('#total_savings').text(humanFileSize(data.savings_total) || 0);
				$('#total_images_processed').text(data.images || 0);
				$('#total_optimization_cycles').text(data.cycles || 0);
				if (data.content) {
					$.get(data.content + '?t=' + new Date().getTime())
						.done(function (file_log) {
							$('#log-output').text(file_log);
							console.log(file_log);
						})
						.fail(function (jqXHR, textStatus, errorThrown) {
							$('#log-output').text('Log File does not exist or some error occurred fetching it.');
							//console.error('Error:', textStatus, errorThrown);
						});
					$('#log-output').scrollTop($('#log-output')[0].scrollHeight);
				}

				let percent;
				if (direction === 'optimization') {
					const total = data.unoptimized_images_number + data.completed;
					percent = (data.completed / (total || 1)) * 100;
					$('#bulk-progress-bar').css('width', percent + '%');
					$('#bulk-progress').text(`${data.completed}/${total} images optimized. Saving: ${humanFileSize(data.savings)}`);
				} else {
					const total = data.optimized_images_number + data.completed;
					percent = (data.completed / (total || 1)) * 100;
					$('#bulk-progress-bar').css('width', percent + '%');
					$('#bulk-progress').text(`${data.completed}/${total} images reversed.`);
				}
				delayMicroseconds(1000);
				if (data.finished || data.cancelled) {
					setProcessingState('completed');
					if (data.cancelled) {
						//$('#bulk-progress').text
						alert('Operation cancelled!');
					} else {
						//$('#bulk-progress').text
						alert(`${direction === 'optimization' ? 'Optimization' : 'Reverse'} complete!`);
					}
					aehSource.close();
					aehSource = null;
				}
			};
		}
	}
	function delayMicroseconds(us) {
		const start = performance.now();
		while (performance.now() - start < us / 1000) {
			// tight loop
		}
	}
	$('#start-optimization').on('click', function () {
		setProcessingState('start');
		$.post(ajaxurl, {
			action: 'aeh_start_optimization',
			nonce: BulkManager.nonce // use localized nonce
		}, function (res) {
			$('#bulk-progress').text('Optimization is starting....');
			if (res.success) {
				startSSE('optimization');
			}
		});
	});
	$('#reset-operation').on('click', function () {
		$.post(ajaxurl, {
			action: 'aeh_reset_operation',
			nonce: BulkManager.nonce // use localized nonce
		}, function (res) {
			if (res.success) {
				$('#bulk-progress').text('Operation is reseted on request!');
				setProcessingState('default');
			}
		});
	});
	$('#start-reverse').on('click', function () {
		setProcessingState('reverse');
		$.post(ajaxurl, {
			action: 'aeh_start_reverse_optimization',
			nonce: BulkManager.nonce
		}, function (res) {
			$('#bulk-progress').text('Reverse optimization is starting....');
			if (res.success) {
				startSSE('reverse');
			}
		});
	});
	$('#cancel-operation').on('click', function () {
		setProcessingState('loading');
		$.post(ajaxurl, {
			action: 'aeh_cancel_operation',
			nonce: BulkManager.nonce
		}, function (res) {
			if (res.success) {

			}
		});

	});
	function humanFileSize(size) {
		var i = size == 0 ? 0 : Math.floor(Math.log(size) / Math.log(1024));
		return +((size / Math.pow(1024, i)).toFixed(0)) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
	}
});