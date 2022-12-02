# Jarimas DPRD Bantul

1. Jalankan : composer install

2. End point send to ckan
base_url()/Ckan_post/send/[UUID OPD]/[Nama dataset]/[Judul dataset]/[Judul Resources]/[Nama file excel]/[status private/public]
    - note: 
        a. excel file = base_url() /assets/docs/
        b. status = private ( true )  , public ( false )
    - example:
        http://localhost/portal-sdi-baru/Ckan_post/send/be0cc93d-1796-4603-97dc-51ccb993face/dataset1/juduldataset1/judulresource1/dikpora.xlsx/true

"# portal-sdi" 
