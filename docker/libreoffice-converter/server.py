from __future__ import annotations

import shutil
import subprocess
import tempfile
from pathlib import Path
from uuid import uuid4

from flask import Flask, jsonify, request, Response


app = Flask(__name__)


@app.get("/health")
def health() -> Response:
    return jsonify({"status": "ok"})


@app.post("/convert")
def convert() -> Response:
    uploaded_file = request.files.get("file")

    if uploaded_file is None or uploaded_file.filename is None or uploaded_file.filename == "":
        return jsonify({"message": "No file uploaded."}), 400

    if not uploaded_file.filename.lower().endswith(".docx"):
        return jsonify({"message": "Only .docx files are supported."}), 400

    request_directory = Path(tempfile.gettempdir()) / f"libreoffice-{uuid4()}"
    request_directory.mkdir(parents=True, exist_ok=True)

    input_path = request_directory / f"{uuid4()}.docx"
    output_path = request_directory / f"{input_path.stem}.pdf"

    try:
        uploaded_file.save(input_path)

        process = subprocess.run(
            [
                "libreoffice",
                "--headless",
                "--convert-to",
                "pdf",
                "--outdir",
                str(request_directory),
                str(input_path),
            ],
            capture_output=True,
            text=True,
            timeout=60,
            check=False,
        )

        if process.returncode != 0:
            details = (process.stderr or process.stdout).strip()
            return jsonify(
                {
                    "message": "LibreOffice conversion failed.",
                    "details": details or "Unknown conversion error.",
                }
            ), 500

        if not output_path.exists():
            return jsonify({"message": "Converted PDF file was not created."}), 500

        pdf_bytes = output_path.read_bytes()

        return Response(
            pdf_bytes,
            mimetype="application/pdf",
            headers={
                "Content-Disposition": f'inline; filename="{input_path.stem}.pdf"',
            },
        )
    except subprocess.TimeoutExpired:
        return jsonify({"message": "LibreOffice conversion timed out."}), 500
    except Exception as exception:
        return jsonify(
            {
                "message": "Unexpected error during conversion.",
                "details": str(exception),
            }
        ), 500
    finally:
        shutil.rmtree(request_directory, ignore_errors=True)


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
