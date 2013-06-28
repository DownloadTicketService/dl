function extraArgs()
{
  return {
    "restURL": { type: "char", value: document.getElementById("restURL").value },
    "username": { type: "char", value: document.getElementById("username").value },
    "password": { type: "char", value: document.getElementById("password").value },
  };  
}
