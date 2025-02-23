<?php
session_start();
$_SESSION["messages"] = $_SESSION["messages"] ?? [];
if ($env = @parse_ini_file(".env")) {
    $_ENV["api-key"] = $env["api-key"];
}
if (isset($_GET["clear"])) {
    session_destroy();
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit();
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($msg = trim($_POST["message"]))) {
    $_SESSION["messages"][] = ["role" => "user", "content" => $msg];
    $ch = curl_init("https://api.x.ai/v1/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            "model" => "grok-2-latest",
            "messages" => $_SESSION["messages"],
            "stream" => false,
        ]),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $_ENV["api-key"],
        ],
    ]);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);
    if (!empty($res["choices"][0]["message"]["content"])) {
        $_SESSION["messages"][] = [
            "role" => "assistant",
            "content" => $res["choices"][0]["message"]["content"],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Grok Chatbot</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
  <style>form::after,textarea {grid-area: 1/1/2/2}form::after{content:attr(data-replicated-value)" ";white-space:pre-wrap;visibility:hidden;}</style>
</head>
<body class="bg-[#f9f8f6]">
<main class="h-dvh flex flex-col">
  <header class="fixed w-full z-40 bg-gradient-to-b from-gray-100 to-transparent p-3">
    <div class="max-w-[50rem] mx-auto flex justify-between items-center">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-[#1C1C1C] flex items-center justify-center">
          <i class="ri-twitter-x-fill text-xl text-white"></i>
        </div>
        <span class="text-xl font-semibold">Grok</span>
      </div>
      <div class="flex items-center gap-4">
          <a href="?clear=1" title="Clear history" class="p-2 hover:bg-gray-200 rounded-full"><i class="ri-edit-line text-xl leading-none"></i></a>
        <button onclick="navigator.share({title:'Grok Chat',text:'Check out my chat with Grok',url:window.location.href})" class="p-2 hover:bg-gray-200 rounded-full">
          <i class="ri-share-2-line text-xl leading-none"></i>
        </button>
        <div class="h-8 w-8 rounded-full bg-violet-500 text-white flex items-center justify-center">M</div>
      </div>
    </div>
  </header>
  <div id="chat-container" class="flex-1 overflow-y-auto px-5 pt-20 pb-40">
    <div class="max-w-[50rem] mx-auto flex flex-col gap-8 pb-4">
      <?php foreach ($_SESSION["messages"] as $m):
          $isUser = $m["role"] === "user"; ?>
        <div class="flex <?= $isUser ? "justify-end" : "justify-start" ?>">
          <div class="max-w-[80%] p-3 <?= $isUser
              ? "bg-blue-500 text-white rounded-l-3xl rounded-t-3xl"
              : "" ?>">
            <?= htmlspecialchars($m["content"] ?? "Error: Missing content") ?>
          </div>
        </div>
      <?php
      endforeach; ?>
    </div>
  </div>
  <div class="fixed bottom-0 w-full max-w-[50rem] left-1/2 -translate-x-1/2 p-3">
      <form method="POST" class="grid relative bg-stone-50 p-2 rounded-3xl ring-1 ring-gray-200 hover:ring-gray-300 hover:shadow hover:bg-white focus-within:ring-gray-300 duration-300" data-replicated-value="">
      <textarea name="message" class="w-full p-3 bg-transparent focus:outline-none" placeholder="How can Grok help?" style="resize:none;" oninput="this.parentNode.dataset.replicatedValue=this.value"></textarea>
      <div class="grid grid-cols-[auto_1fr] gap-2 absolute bottom-4 right-4">
        <select class="rounded-lg border px-3 py-1.5 text-sm"><option>Grok 2</option></select>
        <button id="submit-button" type="submit" disabled class="justify-self-end rounded-full bg-black hover:bg-gray-600 text-white p-2 leading-none disabled:bg-gray-300 duration-300"><i class="ri-arrow-up-line"></i></button>
      </div>
    </form>
  </div>
</main>
<script>
  const a=document.getElementsByName('message')[0],
        b=document.getElementById('submit-button'),
        c=document.getElementById('chat-container');
  a.addEventListener('input',()=>b.disabled=!a.value.trim());
  c.scrollTop=c.scrollHeight;
  document.addEventListener('keydown',e=>e.shiftKey&&e.key==='Enter'?e:e.key==='Enter'?(e.preventDefault(),document.querySelector('form').submit()):0);
</script>
</body>
</html>
