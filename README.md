## img-compress
基于php-cli的图片压缩工具,可1:1将文件夹中所有图片进行压缩

### 说明
输入文件夹可包含多层级目录，经压缩后的图片按源文件夹路径原样输出；

程序只会压缩图片文件，其他格式文件会原样复制至输出文件夹。

### 参数说明
1. /from：原始图片文件夹输入
2. /to：压缩后的图片输出


## 使用方式

### 自建Docker运行
1. 创建docker镜像
```
docker image build -t img-compress .
```
2. 执行
```
docker run -it --rm -v /img/input/dir:/from -v /img/output/dir:/to img-compress
```

### 使用我创建的Docker运行
```
docker run -it --rm -v /img/input/dir:/from -v /img/output/dir:/to yhf7952/img-compress
```

### php环境中运行
下载img.php，并修改输入输出路径，在php中执行

```
php img.php
```

## 更多介绍参见
https://yantuz.cn/img-compress/
