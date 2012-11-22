CC=g++
CFLAGS=-W -Wall -Werror
LDFLAGS=
EXEC=xv25_test
OBJECTS=main xv25 serial

ARCH=x86
TOPDIR=..
SRCDIR = $(TOPDIR)/src
OBJDIR = $(TOPDIR)/obj/$(ARCH)
BINDIR = $(TOPDIR)/bin/$(ARCH)

GREEN:=\033[01;32m
RED:=\033[01;31m
WHITE:=\033[00m

OBJS = $(OBJECTS:%=$(OBJDIR)/%.o)

V=@

all: $(BINDIR)/$(EXEC)

$(BINDIR)/$(EXEC): $(OBJS)
	$(V)test -d $(BINDIR) || mkdir -p $(BINDIR)
	$(V)printf "$(GREEN)[BIN]$(WHITE) $@ \n"; 
	$(V)$(CC) -o $@ $^ $(LDFLAGS) 

$(OBJDIR)/%.o: $(SRCDIR)/%.cc
	$(V)test -d $(OBJDIR) || mkdir -p $(OBJDIR)
	$(V)printf "$(GREEN)[CXX]$(WHITE) $<\n"; 
	$(V)$(CC) -c $< $(CFLAGS) -o $@

.PHONY: clean mrproper

clean:
	$(V)printf "$(RED)[clean]$(WHITE)\n";
	$(V)rm -f $(OBJDIR)/*.o *~

mrproper: clean
	$(V)printf "$(RED)[mrproper]$(WHITE)\n";
	$(V)rm -f $(BINDIR)/$(EXEC)