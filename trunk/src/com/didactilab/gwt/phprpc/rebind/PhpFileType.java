package com.didactilab.gwt.phprpc.rebind;

public enum PhpFileType {

	ONE_FILE		("one-file"),
	BY_CLASS		("by-class"),
	BY_PACKAGE		("by-package");
	
	private final String name;

	private PhpFileType(String name) {
		this.name = name;
	}
	
	@Override
	public String toString() {
		return name;
	}
	
	public static PhpFileType valueOfName(String name) throws IllegalArgumentException {
		for (PhpFileType type : values()) {
			if (type.name.equals(name)) {
				return type;
			}
		}
		throw new IllegalArgumentException(String.format("Cannot convert PhpFileType from the given name : %s", name));
	}
	
}
