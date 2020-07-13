package com.maikoo.businessdirectory.model.query;

import lombok.Data;

import java.util.Objects;

@Data
public class ClassGroupInformationQuery extends GroupInformationQuery {
    private String schoolName;
    private String className;

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (!(o instanceof ClassGroupInformationQuery)) return false;
        if (!super.equals(o)) return false;
        ClassGroupInformationQuery that = (ClassGroupInformationQuery) o;
        return Objects.equals(getSchoolName(), that.getSchoolName()) &&
                Objects.equals(getClassName(), that.getClassName());
    }

    @Override
    public int hashCode() {
        return Objects.hash(super.hashCode(), getSchoolName(), getClassName());
    }
}
