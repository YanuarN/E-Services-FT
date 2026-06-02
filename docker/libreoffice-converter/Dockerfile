FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive
ENV PYTHONUNBUFFERED=1
ENV HOME=/home/converter

RUN apt-get update && apt-get install -y --no-install-recommends \
    libreoffice-writer \
    libreoffice-core \
    python3 \
    python3-pip \
    fonts-liberation \
    fonts-dejavu \
    fonts-noto \
    fonts-crosextra-carlito \
    fonts-crosextra-caladea \
    && rm -rf /var/lib/apt/lists/*

RUN useradd --create-home --shell /bin/bash converter

WORKDIR /app

COPY server.py /app/server.py

RUN pip3 install --no-cache-dir flask
RUN chown -R converter:converter /app /home/converter

USER converter

EXPOSE 5000

ENTRYPOINT ["python3", "server.py"]
