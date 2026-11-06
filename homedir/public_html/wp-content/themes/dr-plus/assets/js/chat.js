(function($) {
	$(document).ready(function(){
		const ChatData = structuredClone(drplusChat);
		const sessionId = ChatData.chatID
		const primaryColor = getPrimaryColor();
		const allowedTypes = ChatData.allowedFileTypes;
		const $chatContainer = $('.chat-messages');
		const pollInterval = parseInt(ChatData.ajaxCheckMessageInterval) * 1000;
		let hasMic = true;
		let lastMessageId = ChatData.lastMessageID;
		let fetchMessagesLock = false;
		let currentUploadXHR = null;
		let mediaRecorder = null;
		let recordedChunks = [];
		let isRecording = false;
		let recordTimerInterval = null;
		let recordStartTime = null;
		let deletedNewMessageSeparator = false;		
		let lastSendedMessageIDs = [];
		let newMessagesCount = 0;
		let isTabVisible = !document.hidden;
		let isTabFocused = document.hasFocus();
		let pollTimeoutId = null;

		// Check for microphone access
		if( ChatData.recordVoice ) {
			if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
				$('#chat-record-voice-btn').hide();
			}
			else {
				navigator.mediaDevices.enumerateDevices().then(function(devices) {
					hasMic = devices.some(function(device) {
						return device.kind === 'audioinput';
					});
					if (!hasMic) {
						$('#chat-record-voice-btn').hide();
					}
				});
			}
		}
		

		// Poll for new messages every 5 seconds
		if (sessionId && ChatData.chatStatus == 'open' && ChatData.ajaxCheckMessage) {
			scheduleNextPoll();
		}

		function scheduleNextPoll() {
			if (pollTimeoutId) {
				clearTimeout(pollTimeoutId);
			}
			
			if (!isTabVisible || !isTabFocused) {
				return; // Don't schedule if tab is not visible/focused
			}

			pollTimeoutId = setTimeout(async function() {
				try {
					await pollNewMessages();
				} catch (error) {
					console.error('Polling error:', error);
				}
				if (isTabVisible && isTabFocused) {
					scheduleNextPoll();
				}
			}, pollInterval);
		}

		// Getting main color
		function getPrimaryColor() {
			var rootElement = $('body')[0]; // or document.documentElement
			var computedStyles = window.getComputedStyle(rootElement);
			return computedStyles.getPropertyValue('--primary-100');
		}		

		async function sendMessage(sessionId, message, type = 'text', fileUrl = null) {
			return chatAPI.sendMessage(sessionId, message, type, fileUrl)
				.then(response => {
					return response;
				})
				.catch(error => {
					console.error('Error sending message:', error);
					if (error.status === 429) {
						// alert('Rate limited: too many messages. Please wait a minute.');
					} else if (error.status === 403) {
						// alert('Permission denied: you cannot send to this conversation.');
					} else if (error.status === 400) {
						// alert('Invalid message: ' + (error.message || 'message too long or empty'));
					}
					throw error;
				});
		}

		async function fetchMessages(sessionId, afterId = 0) {
			if (fetchMessagesLock) return Promise.resolve({ success: false, data: { messages: [] } });
			fetchMessagesLock = true;
			
			try {
				return await chatAPI.getMessages(sessionId, afterId, 1, 50)
					.then(response => {
						return response;
					})
					.catch(error => {
						console.error('Error fetching messages:', error);
						if (error.status === 403) {
							// alert('Permission denied: you cannot access this conversation.');
						}
						throw error;
					});
			} finally {
				fetchMessagesLock = false;
			}
		}

		async function markMessagesSeen(sessionId) {
			return chatAPI.markSeen(sessionId)
				.then(response => {
					return response;
				})
				.catch(error => {
					console.error('Error marking messages seen:', error);
					if (error.status === 403) {
						// alert('Permission denied.');
					}
					throw error;
				});
		}

		function renderMessage(message, userId) {
			const senderType = message.sender_id == userId ? 'current-user' : 'other-user';
			const messageType = message.type;
			
			let template = wp.template(`chat-${senderType}-message-${messageType}`);
			let templateArgs = {
				message: message.message,
				created_at: message.created_at,
				message_id: message.id,
			}
			templateArgs.message = templateArgs.message.replace(/\r\n|\r|\n/g, '<br>');
			if( messageType == 'voice' ) {
				templateArgs.file_url = ChatData.siteUrl + '?chat_file=' + encodeURIComponent(message.file_url);
			} else if( messageType == 'file' ) {
				templateArgs.file_url = message.file_url;
			}	
			
			return template(templateArgs);
		}

		function appendMessages(messages, userId, scrollDown) {		
			if (!messages || !messages.length) return;
			markMessagesSeen(sessionId);
			
			messages.forEach(function(msg) {
				if( lastSendedMessageIDs.indexOf(parseInt(msg.id)) === -1 ) {	
					$chatContainer.append(renderMessage(msg, userId));
				}
				lastMessageId = Math.max(lastMessageId, parseInt(msg.id));
			});
			
			if( scrollDown ) {
				$chatContainer.scrollTop($chatContainer[0].scrollHeight);
			} else {
				// if user is not at the bottom of the chat, show new message notification
				const containerHeight = $chatContainer[0].scrollHeight;
				const scrollTop = $chatContainer.scrollTop();
				const clientHeight = $chatContainer[0].clientHeight;
				if (scrollTop + clientHeight < containerHeight - 50) {
					newMessagesCount += messages.length;
					$('.chat-new-message-count').text(newMessagesCount);
					$('.chat-new-messages-notif').fadeIn({
						complete: function() {
							$(this).css('display', 'flex');
						}
					})
				}
			}
		}

		function pollNewMessages( scrollDown = false ) {
			if (!sessionId) return;
			
			fetchMessages(sessionId, lastMessageId).then(function(res) {
				if (res.success && res.data && res.data.messages && res.data.messages.length) {
					appendMessages(res.data.messages, ChatData.userID, scrollDown);
				}
			});
		}

		function hideUploadProgress(chatID) {			
			let $messageItem = $(`#${chatID}`);
			if (!$messageItem.length) return;
			$messageItem.find('.chat-upload-progress').remove();
			$messageItem.removeClass('progressbar-active');
		}
		function showUploadError(chatID, msg) {
			hideUploadProgress(chatID);
			let $messageItem = $(`#${chatID}`);
			if (!$messageItem.length) return;
			let $err = $messageItem.find('.chat-message-upload-failed');
			$err.text(msg);
			$messageItem.addClass('status-failed');
		}

		function uploadFile(file, type, chatID) {
			_uploadFile(file, chatID, function(chatID, percent) {
				showUploadProgress(chatID, percent);
			}).then(function(res) {				
				hideUploadProgress(chatID);
				if (res.success && res.data && res.data.file_url) {
					sendMessage(sessionId, file.name, type, res.data.file_url).then(function(res2) {
						if (res2.success) {
							$(`#${chatID}`).remove();
							pollNewMessages();
						}
					});
				} else {
					showUploadError(chatID, res.data.message??ChatData.i18n.uploadFailed);
				}
			}).catch(function(err){
				hideUploadProgress(chatID);
				showUploadError(chatID, err);
			});
		}

		function _uploadFile(file,chatID, onProgress) {
			return new Promise(function(resolve, reject) {
				return chatAPI.uploadFile(sessionId, file)
					.then(response => {
						if (onProgress) onProgress(chatID, 1.0);
						resolve(response);
					})
					.catch(error => {
						console.error('Error uploading file:', error);
						if (error.status === 429) {
							reject('Rate limited: too many uploads. Please wait a minute.');
						} else if (error.status === 403) {
							reject('Permission denied: you cannot upload to this conversation.');
						} else if (error.status === 400) {
							reject('Invalid file: ' + (error.message || 'unsupported type or too large'));
						} else {
							reject(ChatData.i18n.uploadFailed);
						}
					});
			});
		}

		// Update upload percent for progressbar
		function showUploadProgress(chatID, percent) {	
			$(`#${chatID}`).find('.chat-upload-progress-wrapper').circleProgress('value', percent);
		}

		// Initial load: get all messages
		(function initialLoad() {
			if (!sessionId || ChatData.chatStatus != 'open') return;
			
			$chatContainer.scrollTop($chatContainer[0].scrollHeight);
			markMessagesSeen(sessionId);
		})();

		function removeNewMessagesSeparator() {
			if(deletedNewMessageSeparator) return;
			$('.chat-new-messages-separator').fadeIn({
				complete: function() {
					$(this).remove()
				}
			});
		}

		// Handle tab visibility changes
		document.addEventListener('visibilitychange', function() {
			isTabVisible = !document.hidden;
			
			if (isTabVisible && isTabFocused) {
				scheduleNextPoll();
			}
		});

		// Handle tab focus changes
		window.addEventListener('focus', function() {			
			isTabFocused = true;
			if (isTabVisible) {
				scheduleNextPoll();
			}
		});

		window.addEventListener('blur', function() {
			isTabFocused = false;
		});

		// Hide new messages notif when user scroll down at bottom of section
		$chatContainer.on('scroll', function() {
			const container = $chatContainer[0];
			if (container.scrollTop + container.clientHeight >= container.scrollHeight - 2) {
				$('.chat-new-messages-notif').fadeOut();
				newMessagesCount = 0;
			}
		});

		// Send text message
		$('#chat-send-btn').on('click', function() {
			const $input = $('#chat-send-input');
			const message = $input.val().trim();
			if (!message) return;
			removeNewMessagesSeparator();

			// render message
			const now = new Date();
			let hours = String(now.getHours()); // Returns the hour (0-23)
			let minutes = String(now.getMinutes()); // Returns the minute (0-59)
			let messageUniqueID = drplus.uniqueId();
			$chatContainer.append(renderMessage({
				id: messageUniqueID,
				type: 'text',
				sender_id: ChatData.userID,
				message: message,
				created_at: `${hours.padStart(2, "0")}:${minutes.padStart(2, "0")}`
			}, ChatData.userID));
			$chatContainer.scrollTop($chatContainer[0].scrollHeight);
			$input.val('').trigger('change');

			sendMessage(sessionId, message, 'text').then(function(res) {
				if (res.success) {
					$(`.chat-message[data-message-id=${messageUniqueID}]`).attr('data-message-id', res.data.id);
					lastSendedMessageIDs.push(res.data.id)
					pollNewMessages( true );
				}
			}).catch(function() {
				$input.val(message).trigger('change');
				let $failedMessage = $(`.chat-message[data-message-id=${messageUniqueID}]`);				
				$failedMessage.find('.chat-message-text-error-text').text(ChatData.i18n.sendMessageFailed).parent().css('display', 'flex');
			});;
		});

		// Send file message
		$('#chat-send-attachment-btn').on('click', function() {
			removeNewMessagesSeparator();
			$('#chat-send-attachment').val(''); // reset file input
			$('#chat-send-attachment').trigger('click');
		});

		// upload file
		$('#chat-send-attachment').on('change', function(e) {
			const file = e.target.files[0];
			if (!file) return;
			
			if (!allowedTypes.includes(file.type)) {
				// alert(ChatData.i18n.invalidFileType);
				return;
			}

			// Show file message with progress bar			
			let chatID = drplus.uniqueId();
			const now = new Date();
			let template = wp.template(`chat-current-user-message-file`);
			let templateArgs = {
				message: ChatData.i18n.uploading,
				created_at: `${drplus.addZero(now.getHours())}:${drplus.addZero(now.getMinutes())}`,
				chat_id: chatID,
				file_url: ""
			}
			$chatContainer.append(template(templateArgs));
			$chatContainer.scrollTop($chatContainer[0].scrollHeight);
			$(`#${chatID}`).addClass('progressbar-active');
			$b = $(`#${chatID}`).find('.chat-upload-progress-wrapper').circleProgress({
				size: 36,
				value: 0,
				lineCap: 'round',
				startAngle: -Math.PI / 2,
				fill: {
					color: primaryColor
				}
			});

			uploadFile(file, 'file', chatID);
		});


		// Cancel file uploading
		$(document).on('click', '.chat-message-upload-cancel', function() {
			if (currentUploadXHR) {
				currentUploadXHR.abort();
				currentUploadXHR = null;
			}
		});

		// Set chat input rows and Toggle display send/record icon
		$('#chat-send-input').on('keyup change', function(e) {
			if (e.key === 'Enter' && e.ctrlKey) {
				e.preventDefault();
				$('#chat-send-btn').trigger('click');
				return;
			}

			// Set rows
			let el = $(this)[0];
			let lineNumber = el.value.substr(0, el.selectionStart).split("\n").length;
			lineNumber = lineNumber > 3 ? 3 : lineNumber;
			$(this).attr('rows', lineNumber);
			
			// Toggle display send/record icon		
			if(!el.value) {				
				$('.chat-send-container').removeClass('text');
			} else {
				$('.chat-send-container').addClass('text');
			}
		});
		
		// Download file on file icon click
		$(document).on('click', '.chat-message-file', function(e) {
			e.preventDefault();
			const fileUrl = $(this).data('url');
			
			if (!fileUrl) return;
			// Use secure endpoint (function/chat.php with ?chat_file=...)
			const downloadUrl = ChatData.siteUrl + '?chat_file=' + encodeURIComponent(fileUrl);
			window.open(downloadUrl, '_blank');
		});

		$('.chat-new-messages-notif').on('click', function() {
			$chatContainer.scrollTop($chatContainer[0].scrollHeight);
			$(this).fadeOut();
			newMessagesCount = 0;
		})

		function formatRecordTime(seconds) {
			const m = Math.floor(seconds / 60).toString().padStart(2, '0');
			const s = (seconds % 60).toString().padStart(2, '0');
			return m + ':' + s;
		}

		$(document).on('click', '#chat-record-voice-btn', function() {
			removeNewMessagesSeparator();
			if (isRecording) return;
			if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
				// alert(ChatData.i18n.voiceNotSupported);
				return;
			}
			// Check for mic permission before starting recording
			navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
				mediaRecorder = new MediaRecorder(stream);
				recordedChunks = [];
				mediaRecorder.ondataavailable = function(e) {
					if (e.data.size > 0) recordedChunks.push(e.data);
				};
				mediaRecorder.onstop = function() {
					stream.getTracks().forEach(track => track.stop());
				};
				mediaRecorder.start();
				isRecording = true;
				$('.chat-send-container').addClass('voice');
				// Start timer
				recordStartTime = Date.now();
				$('.chat-send-recording-voice-time').text('00:00');
				recordTimerInterval = setInterval(function() {
					let elapsed = Math.floor((Date.now() - recordStartTime) / 1000);
					$('.chat-send-recording-voice-time').text(formatRecordTime(elapsed));
				}, 1000);
			}).catch(function(err) {
				// alert(ChatData.i18n.micPermissionDenied);
			});
		});

		$(document).on('click', '#chat-cancel-record-voice-btn', function() {
			if (mediaRecorder && isRecording) {
				mediaRecorder.stop();
				isRecording = false;
			}
			$('.chat-send-container').removeClass('voice');
			clearInterval(recordTimerInterval);
			$('.chat-send-recording-voice-time').text('');
		});

		$(document).on('click', '#chat-send-voice-btn', function() {
			if (!mediaRecorder || !isRecording) return;
			mediaRecorder.stop();
			mediaRecorder.onstop = function() {
				isRecording = false;
				$('.chat-send-container').removeClass('voice');
				clearInterval(recordTimerInterval);
				$('.chat-send-recording-voice-time').text('');
				// Create blob and upload
				const blob = new Blob(recordedChunks, { type: 'audio/webm' });
				let chatID = drplus.uniqueId();
				const now = new Date();
				let template = wp.template(`chat-current-user-message-file`);
				let templateArgs = {
					message: ChatData.i18n.uploadingVoice,
					created_at: `${drplus.addZero(now.getHours())}:${drplus.addZero(now.getMinutes())}`,
					chat_id: chatID,
					file_url: ""
				};
				$chatContainer.append(template(templateArgs));
				$chatContainer.scrollTop($chatContainer[0].scrollHeight);
				$(`#${chatID}`).addClass('progressbar-active');
				$b = $(`#${chatID}`).find('.chat-upload-progress-wrapper').circleProgress({
					size: 36,
					value: 0,
					lineCap: 'round',
					startAngle: -Math.PI / 2,
					fill: { color: primaryColor }
				});
				// Upload
				const file = new File([blob], 'voice_' + Date.now() + '.webm', { type: 'audio/webm' });
				uploadFile(file, 'voice', chatID);
			};
		});

		$('.chat-header-action-icon').on('click', function() {
			$(this).parent().toggleClass('open');
		});

		// close chat header actions on outside click
		$(document).on('click', function(e) {
			if (!$(e.target).closest('.chat-header-action-icon').length) {
				$('.chat-header-action-wrap.open').removeClass('open');
			}
		});
	});
})(jQuery);