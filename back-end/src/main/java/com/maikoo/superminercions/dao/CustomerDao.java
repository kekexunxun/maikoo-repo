package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.CustomerDO;
import org.apache.ibatis.annotations.*;

import java.math.BigDecimal;
import java.util.List;

@Mapper
public interface CustomerDao {
    @Update("UPDATE c_user SET password = #{password} WHERE phone = #{phone}")
    int resetPassword(@Param("password") String password, @Param("phone") String phone);

    int updateBalance(CustomerDO customerDO);

    @Update("UPDATE " +
                "c_user " +
            "SET " +
                "smc_balance = smc_balance + #{performance}, " +
                "available_smc_balance = available_smc_balance + #{performance} " +
            "WHERE " +
                "id = #{id}")
    int updateSMCBalance(@Param("id") long id, @Param("performance") BigDecimal performance);

    @ResultMap("customerResultMap")
    @Select("SELECT " +
                "balance, " +
                "available_balance, " +
                "smc_balance, " +
                "available_smc_balance, " +
                "eth_balance, " +
                "available_eth_balance " +
            "FROM " +
                "c_user " +
            "WHERE " +
                "id = #{customerUserId}")
    CustomerDO selectBalance(long customerUserId);

    @ResultMap("customerResultMap")
    @Select("SELECT " +
                "is_updated_password, " +
                "is_updated_trading_password, " +
                "is_updated_authentication, " +
                "is_bind_ali, " +
                "is_bind_bank " +
            "FROM " +
                "c_user " +
            "WHERE " +
                "id = #{customerUserId}")
    CustomerDO selectFlag(long customerUserId);

    @ResultMap("customerResultMap")
    @Select("SELECT " +
                "id," +
                "phone," +
                "is_disable " +
            "FROM " +
                "c_user " +
            "WHERE " +
                "username = #{username} " +
                "AND password = #{password}")
    CustomerDO login(@Param("username") String username, @Param("password") String password);

    @Select("SELECT wallet FROM c_user WHERE id = #{customerUserId}")
    String walletInformation(long customerUserId);

    @Update("UPDATE c_user SET wallet = #{wallet} WHERE id = #{customerUserId}")
    int updateWallet(@Param("customerUserId") long customerUserId, @Param("wallet") String wallet);

    @Update("UPDATE " +
                "c_user " +
            "SET " +
                "password = #{password}, " +
                "password_phone = #{passwordPhone}, " +
                "is_updated_password = 1, " +
                "updated_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
                "id = #{id}")
    int updatePassword(CustomerDO customerDO);

    @Update("UPDATE " +
                "c_user " +
            "SET " +
                "trading_password = #{tradingPassword}, " +
                "trading_password_phone = #{tradingPasswordPhone}, " +
                "is_updated_trading_password = 1, " +
                "updated_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
                "id = #{id}")
    int updateTradingPassword(CustomerDO customerDO);

    @ResultMap("customerResultMap")
    @Select("SELECT ali FROM c_user WHERE id = #{id}")
    CustomerDO selectAli(long id);

    @Update("UPDATE " +
                "c_user " +
            "SET " +
                "ali = #{ali}, " +
                "is_bind_ali = 1, " +
                "updated_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
                "id = #{id}")
    int updateAli(CustomerDO customerDO);

    @ResultMap("customerResultMap")
    @Select("SELECT bank, bank_branch, bank_card_number FROM c_user WHERE id = #{id}")
    CustomerDO selectBank(long id);

    @Update("UPDATE " +
                "c_user " +
            "SET " +
                "bank = #{bank}, " +
                "bank_branch = #{bankBranch}, " +
                "bank_card_number = #{bankCardNumber}, " +
                "is_bind_bank = 1, " +
                "updated_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
                "id = #{id}")
    int updateBank(CustomerDO customerDO);

    @ResultMap("customerResultMap")
    @Select("SELECT username, name, phone, id_card, front_id_card_uri, back_id_card_uri FROM c_user WHERE id = #{id}")
    CustomerDO selectAuthentication(long id);

    @Update("UPDATE " +
                "c_user " +
            "SET " +
                "is_updated_authentication = 1, " +
                "account = #{account}, " +
                "name = #{name}, " +
                "phone = #{phone}, " +
                "id_card = #{idCard}, " +
                "front_id_card_uri = #{frontIdCardUri}, " +
                "back_id_card_uri = #{backIdCardUri}, " +
                "updated_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
                "id = #{id}")
    int updateAuthentication(CustomerDO customerDO);

    @Select("SELECT " +
            "IF " +
                "( trading_password = #{tradingPassword}, 1, 0 ) " +
            "FROM " +
                "c_user " +
            "WHERE " +
                "id = #{customerUserId}")
    boolean checkTradingPassword(@Param("customerUserId") long customerUserId, @Param("tradingPassword") String tradingPassword);

    @ResultMap("customerResultMap")
    @Select("SELECT " +
                "id, " +
                "username, " +
                "name, " +
                "phone, " +
                "is_disable, " +
                "created_at " +
            "FROM " +
                "c_user " +
            "WHERE " +
                "deleted_at IS NULL")
    List<CustomerDO> selectAll();

    @Insert("INSERT " +
            "INTO " +
                "c_user" +
                "(username, password, trading_password, name, phone, created_at) " +
            "VALUES" +
                "(#{username}, #{password}, #{tradingPassword}, #{name}, #{phone}, UNIX_TIMESTAMP(NOW()))")
    int insert(CustomerDO customerDO);

    @Update("UPDATE " +
                "c_user " +
            "SET " +
                "name = #{name}, " +
                "phone = #{phone}, " +
                "updated_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
                "id = #{id}")
    int update(CustomerDO customerDO);

    @Update("UPDATE " +
                "c_user " +
            "SET " +
                "is_disable = #{isDisable}, " +
                "updated_at = UNIX_TIMESTAMP(NOW()) " +
            "WHERE " +
                "id = #{id}")
    int updateStatus(CustomerDO customerDO);

    @ResultMap("customerResultMap")
    @Select("SELECT " +
                "id, " +
                "name, " +
                "phone, " +
                "smc_balance, " +
                "eth_balance, " +
                "created_at " +
            "FROM " +
                "c_user " +
            "WHERE " +
                "deleted_at IS NULL")
    List<CustomerDO> selectAllWithBalance();

    @ResultMap("customerResultMap")
    @Select("SELECT " +
                "id, " +
                "name, " +
                "phone, " +
                "id_card, " +
                "ali, " +
                "bank, " +
                "bank_card_number, " +
                "wallet "+
            "FROM " +
                "c_user " +
            "WHERE " +
                "deleted_at IS NULL")
    List<CustomerDO> selectAllWithBase();

    @ResultMap("customerResultMap")
    @Select("SELECT " +
                "id, " +
                "name, " +
                "phone, " +
                "id_card, " +
                "ali, " +
                "bank, " +
                "bank_branch, " +
                "bank_card_number, " +
                "wallet,"+
                "front_id_card_uri,"+
                "back_id_card_uri "+
            "FROM " +
                "c_user " +
            "WHERE " +
                "deleted_at IS NULL " +
                "AND id = #{id}")
    CustomerDO selectBase(long id);

    @Select("SELECT " +
                "IF(password=#{newPassword}, 1, 0) " +
            "FROM " +
                "c_user " +
            "WHERE " +
                "id = #{id}")
    boolean checkOldPassword(@Param("id") long id, @Param("newPassword") String newPassword);

    @Select("SELECT " +
                "IF(trading_password=#{newTradingPassword}, 1, 0) " +
            "FROM " +
                "c_user " +
            "WHERE " +
                "id = #{id}")
    boolean checkOldTradingPassword(@Param("id") long id, @Param("newTradingPassword") String newTradingPassword);

    @Select("SELECT " +
                "phone " +
            "FROM " +
                "c_user " +
            "WHERE " +
                "phone = #{phone}")
    String isExistedPhone(String phone);
}
