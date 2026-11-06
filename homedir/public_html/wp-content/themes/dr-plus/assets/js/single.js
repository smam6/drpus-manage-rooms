document.addEventListener("DOMContentLoaded", () => {
	if(document.getElementById('post-share-copy')) {
		document.getElementById('post-share-copy').addEventListener('click', function(e) {
			e.preventDefault();
			let input = document.getElementById('post-share-shortlink');
			input.select();
			input.setSelectionRange(0, 9999); // Mobile
			navigator.clipboard.writeText(input.value);
			document.getElementById('post-share-copy-icon').style.display = 'none';
			document.getElementById('post-share-copy-tick').style.display = 'block';
			setTimeout(() => {
				document.getElementById('post-share-copy-icon').style.display = 'block';
				document.getElementById('post-share-copy-tick').style.display = 'none';
			}, 2000)
		})
	}
});