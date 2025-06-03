import argparse
import subprocess
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent
CORE_DIR = BASE_DIR / "core"
INSTALL_DIR = BASE_DIR / "install"
SUPERVISOR_SH = CORE_DIR / "supervisor.sh"
INSTALL_SH = INSTALL_DIR / "init_packages.sh"


def run_script(script, args=None):
    cmd = ["bash", str(script)]
    if args:
        cmd.extend(args)
    subprocess.run(cmd, check=True)


def init_command():
    """Install dependencies and initialize supervisor."""
    if INSTALL_SH.exists():
        run_script(INSTALL_SH)
    run_script(SUPERVISOR_SH, ["init"])


def delegate_command(command, instance=None):
    args = [command]
    if instance:
        args.append(instance)
    run_script(SUPERVISOR_SH, args)


def main():
    parser = argparse.ArgumentParser(description="MySQL Supervisor CLI")
    sub = parser.add_subparsers(dest="command")

    sub.add_parser("init", help="Initialize environment")
    sub.add_parser("create", help="Create new instance")
    sub.add_parser("list", help="List instances")

    for cmd in ["start", "stop", "purge", "status", "logs", "backup"]:
        p = sub.add_parser(cmd, help=f"{cmd} instance")
        if cmd != "list":
            p.add_argument("name", nargs="?", help="Instance name")

    args = parser.parse_args()

    if args.command == "init":
        init_command()
    elif args.command in {"create", "list"}:
        delegate_command(args.command)
    elif args.command in {"start", "stop", "purge", "status", "logs", "backup"}:
        if not args.name:
            parser.error("the following arguments are required: name")
        delegate_command(args.command, args.name)
    else:
        parser.print_help()


if __name__ == "__main__":
    main()
