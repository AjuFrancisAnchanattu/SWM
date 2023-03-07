function setValue(target)
{
	alert("changed - value: " + target.value);
	document.getElementById("result").value += target.value;
}