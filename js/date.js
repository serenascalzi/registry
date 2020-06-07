$(document).ready(function() {
	let date = new Date()

	let year = date.getFullYear()

	let copyright = `<p>&copy; ${year} Serena Scalzi</p>`
	$('#copyright').html(copyright)
})
