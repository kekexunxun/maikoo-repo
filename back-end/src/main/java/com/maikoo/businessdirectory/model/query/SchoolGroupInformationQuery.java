package com.maikoo.businessdirectory.model.query;

import lombok.Data;

import java.util.Objects;

@Data
public class SchoolGroupInformationQuery extends GroupInformationQuery {
    private String schoolName;

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (!(o instanceof SchoolGroupInformationQuery)) return false;
        if (!super.equals(o)) return false;
        SchoolGroupInformationQuery that = (SchoolGroupInformationQuery) o;
        return Objects.equals(getSchoolName(), that.getSchoolName());
    }

    @Override
    public int hashCode() {
        return Objects.hash(super.hashCode(), getSchoolName());
    }
}
