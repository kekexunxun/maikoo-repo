package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.CustomerProductDO;
import org.apache.ibatis.annotations.*;

import java.math.BigDecimal;
import java.util.List;

@Mapper
public interface CustomerProductDao {
    @ResultMap("customerProductResultMap")
    @Select("SELECT " +
                "cu.id AS c_user_id, " +
                "cup.id, " +
                "cup.user_product_sn, " +
                "cup.image_uri, " +
                "cup.product_number, " +
                "cup.model, " +
                "cup.name, " +
                "cup.performance, " +
                "cup.output, " +
                "cup.is_disable, " +
                "cup.created_at " +
            "FROM " +
                "c_user_product cup " +
                "JOIN c_user cu ON cup.c_user_id = cu.id " +
                "AND cup.deleted_at IS NULL " +
            "WHERE " +
                "user_product_sn = #{userProductSN}")
    CustomerProductDO selectOneByUserProductSN(String userProductSN);

    @ResultMap("customerProductResultMap")
    @Select("SELECT " +
                "cu.id AS c_user_id, " +
                "cu.name AS c_user_name, " +
                "cu.phone AS c_user_phone, " +
                "cup.id, " +
                "cup.user_product_sn, " +
                "cup.image_uri, " +
                "cup.product_number, " +
                "cup.model, " +
                "cup.name, " +
                "cup.performance, " +
                "cup.output, " +
                "cup.is_disable, " +
                "cup.created_at " +
            "FROM " +
                "c_user_product cup " +
                "JOIN c_user cu ON cup.c_user_id = cu.id " +
                "AND cup.deleted_at IS NULL " +
            "WHERE " +
                "cu.id = #{userId}")
    List<CustomerProductDO> selectByUser(long userId);

    @ResultMap("customerProductResultMap")
    @Select("SELECT " +
                "cu.id AS c_user_id, " +
                "cu.name AS c_user_name, " +
                "cu.phone AS c_user_phone, " +
                "cup.id, " +
                "cup.user_product_sn, " +
                "cup.image_uri, " +
                "cup.product_number, " +
                "cup.model, " +
                "cup.name, " +
                "cup.performance, " +
                "cup.output, " +
                "cup.is_disable, " +
                "cup.created_at " +
            "FROM " +
                "c_user_product cup " +
                "JOIN c_user cu ON cup.c_user_id = cu.id " +
                "AND cup.deleted_at IS NULL ")
    List<CustomerProductDO> selectAll();

    @ResultMap("customerProductResultMap")
    @Select("SELECT " +
                "cu.id AS c_user_id, " +
                "cup.id, " +
                "cup.performance " +
            "FROM " +
                "c_user_product cup " +
                "JOIN c_user cu ON cup.c_user_id = cu.id " +
                "AND cup.deleted_at IS NULL " +
            "WHERE " +
                "cup.is_disable = 0 " +
                "AND cup.deleted_at IS NULL")
    List<CustomerProductDO> selectCountByEnable();

    @Update("UPDATE " +
                "c_user_product " +
            "SET " +
                "output = output + #{performance} " +
            "WHERE " +
                "id = #{id} " +
                "AND deleted_at IS NULL")
    int updateOutput(@Param("id") long id, @Param("performance") BigDecimal performance);

    @Update("UPDATE " +
                "c_user_product " +
            "SET " +
                "is_disable = #{isDisable}, " +
                "updated_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
                "id = #{id} ")
    int updateStatus(CustomerProductDO customerProductDO);

    @Update("UPDATE " +
                "c_user_product " +
            "SET " +
                "performance = #{performance} " +
            "WHERE " +
                "product_number = #{productNumber}")
    int updatePerformance(CustomerProductDO customerProductDO);

    @Update("UPDATE " +
                "c_user_product " +
            "SET " +
                "name = #{name}, " +
                "model = #{model}, " +
                "performance = #{performance}, " +
                "updated_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
                "id = #{id} ")
    int update(CustomerProductDO customerProductDO);

    @Insert("INSERT " +
            "INTO " +
                "c_user_product" +
                "(c_user_id, user_product_sn, image_uri, product_number, model, name, performance, created_at) " +
            "VALUES" +
                "(#{customerDO.id}, #{userProductSN}, #{imageUri}, #{productNumber}, #{model}, #{name}, #{performance}, UNIX_TIMESTAMP(NOW()))")
    int insert(CustomerProductDO customerProductDO);
}
