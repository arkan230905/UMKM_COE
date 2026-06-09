import re

with open('app/Http/Controllers/ProdukController.php', 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace("'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:10240'", "'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:10240'")
content = content.replace("'foto' => 'required|image|mimes:jpg,jpeg,png|max:10240'", "'foto' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:10240'")

with open('app/Http/Controllers/ProdukController.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("patched produk controller foto validation")
