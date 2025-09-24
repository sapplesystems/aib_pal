<?php
system("llama-cli --threads 4 --threads-batch 4 -m /mnt/data/llama_cpp_models/Meta-Llama-3-8B-Instruct-Q4_0.gguf -no-cnv --prompt \"Who is SmallTownPapers?\\n\\n\"");
?>
