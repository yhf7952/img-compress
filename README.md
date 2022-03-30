## img-compress
基于php-cli的图片压缩工具,可1:1将文件夹中所有图片进行压缩

## 使用方式

### 自建Docker运行
1. 创建docker镜像
```
docker image build -t img-compress .
```
2. 执行
```
docker run -it --rm -v /img/input/dir:/from -v //img/output/dir:/to img-compress
```

### 使用我创建的Docker运行
```
docker run -it --rm -v /img/input/dir:/from -v //img/output/dir:/to yhf7952/img-compress
```

### php环境中运行
下载img.php，并修改输入输出路径，在php中执行

```
php img.php
```
